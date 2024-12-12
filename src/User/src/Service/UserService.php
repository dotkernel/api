<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Message;
use Api\App\Repository\OAuthAccessTokenRepository;
use Api\App\Repository\OAuthRefreshTokenRepository;
use Api\User\Collection\UserCollection;
use Api\User\Entity\User;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserResetPassword;
use Api\User\Enum\UserStatusEnum;
use Api\User\Repository\UserDetailRepository;
use Api\User\Repository\UserRepository;
use Api\User\Repository\UserResetPasswordRepository;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Log\LoggerInterface;
use Dot\Mail\Exception\MailException;
use Dot\Mail\Service\MailService;
use Mezzio\Template\TemplateRendererInterface;
use RuntimeException;

use function date;
use function in_array;
use function sprintf;

class UserService implements UserServiceInterface
{
    #[Inject(
        UserRoleServiceInterface::class,
        MailService::class,
        TemplateRendererInterface::class,
        OAuthAccessTokenRepository::class,
        OAuthRefreshTokenRepository::class,
        UserRepository::class,
        UserDetailRepository::class,
        UserResetPasswordRepository::class,
        "dot-log.default_logger",
        "config",
    )]
    public function __construct(
        protected UserRoleServiceInterface $userRoleService,
        protected MailService $mailService,
        protected TemplateRendererInterface $templateRenderer,
        protected OAuthAccessTokenRepository $oAuthAccessTokenRepository,
        protected OAuthRefreshTokenRepository $oAuthRefreshTokenRepository,
        protected UserRepository $userRepository,
        protected UserDetailRepository $userDetailRepository,
        protected UserResetPasswordRepository $userResetPasswordRepository,
        protected LoggerInterface $logger,
        protected array $config = [],
    ) {
    }

    public function activateUser(User $user): User
    {
        return $this->userRepository->saveUser($user->activate());
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function createUser(array $data = []): User
    {
        if ($this->exists($data['identity'])) {
            throw new ConflictException(Message::DUPLICATE_IDENTITY);
        }

        if ($this->emailExists($data['detail']['email'])) {
            throw new ConflictException(Message::DUPLICATE_EMAIL);
        }

        $detail = (new UserDetail())
            ->setFirstName($data['detail']['firstName'] ?? null)
            ->setLastName($data['detail']['lastName'] ?? null)
            ->setEmail($data['detail']['email']);

        $user = (new User())
            ->setDetail($detail)
            ->setIdentity($data['identity'])
            ->usePassword($data['password'])
            ->setStatus($data['status'] ?? UserStatusEnum::Pending);
        $detail->setUser($user);

        if (! empty($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                $user->addRole(
                    $this->userRoleService->findOneBy(['uuid' => $roleData['uuid']])
                );
            }
        }

        return $this->userRepository->saveUser($user);
    }

    public function revokeTokens(User $user): void
    {
        $accessTokens = $this->oAuthAccessTokenRepository->findAccessTokens($user->getIdentity());
        foreach ($accessTokens as $accessToken) {
            $this->oAuthAccessTokenRepository->revokeAccessToken($accessToken->getToken());
            $this->oAuthRefreshTokenRepository->revokeRefreshToken($accessToken->getToken());
        }
    }

    /**
     * @throws RuntimeException
     */
    public function deleteUser(User $user): User
    {
        $this->revokeTokens($user);

        return $this->anonymizeUser($user->setStatus(UserStatusEnum::Deleted));
    }

    /**
     * @throws RuntimeException
     */
    public function anonymizeUser(User $user): User
    {
        $placeholder = $this->getAnonymousPlaceholder();

        $user
            ->setIdentity($placeholder . $this->config['userAnonymizeAppend'])
            ->getDetail()
                ->setFirstName($placeholder)
                ->setLastName($placeholder)
                ->setEmail($placeholder);

        return $this->userRepository->saveUser($user);
    }

    public function exists(string $identity = ''): bool
    {
        try {
            $this->findOneBy(['identity' => $identity]);

            return true;
        } catch (NotFoundException) {
            return false;
        }
    }

    public function existsOther(string $identity = '', string $uuid = ''): bool
    {
        try {
            $user = $this->findOneBy(['identity' => $identity]);

            return $user->getUuid()->toString() !== $uuid;
        } catch (NotFoundException) {
            return false;
        }
    }

    public function emailExists(string $email = ''): bool
    {
        try {
            $this->findByEmail($email);

            return true;
        } catch (NotFoundException) {
            return false;
        }
    }

    public function emailExistsOther(string $email = '', string $uuid = ''): bool
    {
        try {
            $user = $this->findByEmail($email);

            return $user->getUuid()->toString() !== $uuid;
        } catch (NotFoundException) {
            return false;
        }
    }

    /**
     * @throws NotFoundException
     */
    public function findResetPasswordByHash(?string $hash): UserResetPassword
    {
        $userResetPassword = $this->userResetPasswordRepository->findOneBy(['hash' => $hash]);
        if (! $userResetPassword instanceof UserResetPassword) {
            throw new NotFoundException(sprintf(Message::RESET_PASSWORD_NOT_FOUND, (string) $hash));
        }

        return $userResetPassword;
    }

    /**
     * @throws NotFoundException
     */
    public function findByEmail(string $email): User
    {
        $user = $this->userDetailRepository->findOneBy(['email' => $email])?->getUser();
        if (! $user instanceof User || $user->isDeleted()) {
            throw new NotFoundException(Message::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @throws NotFoundException
     */
    public function findByIdentity(string $identity): ?User
    {
        return $this->findOneBy(['identity' => $identity]);
    }

    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params = []): User
    {
        $user = $this->userRepository->findOneBy($params);
        if (! $user instanceof User || $user->isDeleted()) {
            throw new NotFoundException(Message::USER_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @throws BadRequestException
     */
    public function getUsers(array $params = []): UserCollection
    {
        $values = [
            'user.identity',
            'user.status',
            'user.created',
            'user.updated',
        ];

        $params['order'] = $params['order'] ?? 'user.created';
        if (! in_array($params['order'], $values)) {
            throw (new BadRequestException())->setMessages([sprintf(Message::INVALID_VALUE, 'order')]);
        }

        return $this->userRepository->getUsers($params);
    }

    /**
     * @throws MailException
     */
    public function sendActivationMail(User $user): bool
    {
        if ($user->isActive()) {
            return false;
        }

        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        $this->mailService->setSubject('Welcome to ' . $this->config['application']['name']);
        $this->mailService->setBody(
            $this->templateRenderer->render('user::activate', [
                'config' => $this->config,
                'user'   => $user,
            ])
        );

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getDetail()->getEmail()));
        }
    }

    /**
     * @throws MailException
     */
    public function sendResetPasswordRequestedMail(User $user): bool
    {
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        $this->mailService->setSubject(
            'Reset password instructions for your ' . $this->config['application']['name'] . ' account'
        );
        $this->mailService->setBody(
            $this->templateRenderer->render('user::reset-password-requested', [
                'config' => $this->config,
                'user'   => $user,
            ])
        );

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getDetail()->getEmail()));
        }
    }

    /**
     * @throws MailException
     */
    public function sendResetPasswordCompletedMail(User $user): bool
    {
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        $this->mailService->setSubject(
            'You have successfully reset the password for your ' . $this->config['application']['name'] . ' account'
        );
        $this->mailService->setBody(
            $this->templateRenderer->render('user::reset-password-completed', [
                'config' => $this->config,
                'user'   => $user,
            ])
        );

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getDetail()->getEmail()));
        }
    }

    /**
     * @throws MailException
     */
    public function sendWelcomeMail(User $user): bool
    {
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        $this->mailService->setSubject('Welcome to ' . $this->config['application']['name']);
        $this->mailService->setBody(
            $this->templateRenderer->render('user::welcome', [
                'config' => $this->config,
                'user'   => $user,
            ])
        );

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getDetail()->getEmail()));
        }
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function updateUser(User $user, array $data = []): User
    {
        if (isset($data['identity'])) {
            if ($this->existsOther($data['identity'], $user->getUuid()->toString())) {
                throw new ConflictException(Message::DUPLICATE_IDENTITY);
            }
            $user->setIdentity($data['identity']);
        }

        if (isset($data['detail']['email'])) {
            if ($this->emailExistsOther($data['detail']['email'], $user->getUuid()->toString())) {
                throw new ConflictException(Message::DUPLICATE_EMAIL);
            }
        }

        if (isset($data['password'])) {
            $user->usePassword($data['password']);
        }

        if (isset($data['status'])) {
            $user->setStatus($data['status']);
        }

        if (isset($data['hash'])) {
            $user->setHash($data['hash']);
        }

        if (isset($data['detail']['firstName'])) {
            $user->getDetail()->setFirstname($data['detail']['firstName']);
        }

        if (isset($data['detail']['lastName'])) {
            $user->getDetail()->setLastName($data['detail']['lastName']);
        }

        if (isset($data['detail']['email'])) {
            if (! $this->emailExists($data['detail']['email'])) {
                $user->getDetail()->setEmail($data['detail']['email']);
            }
        }

        if (! empty($data['roles'])) {
            $user->resetRoles();
            foreach ($data['roles'] as $roleData) {
                $user->addRole(
                    $this->userRoleService->findOneBy(['uuid' => $roleData['uuid']])
                );
            }
        }

        if (! $user->hasRoles()) {
            throw (new BadRequestException())->setMessages([Message::RESTRICTION_ROLES]);
        }

        return $this->userRepository->saveUser($user);
    }

    /**
     * @throws MailException
     */
    public function sendRecoverIdentityMail(User $user): bool
    {
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        $this->mailService->setSubject(
            'Recover identity for your ' . $this->config['application']['name'] . ' account'
        );
        $this->mailService->setBody(
            $this->templateRenderer->render('user::recover-identity-requested', [
                'config' => $this->config,
                'user'   => $user,
            ])
        );

        try {
            return $this->mailService->send()->isValid();
        } catch (MailException $exception) {
            $this->logger->err($exception->getMessage());
            throw new MailException(sprintf(Message::MAIL_NOT_SENT_TO, $user->getDetail()->getEmail()));
        }
    }

    private function getAnonymousPlaceholder(): string
    {
        return 'anonymous' . date('dmYHis');
    }
}
