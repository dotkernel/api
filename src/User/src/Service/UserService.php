<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Message;
use Api\App\Repository\OAuthAccessTokenRepository;
use Api\App\Repository\OAuthRefreshTokenRepository;
use Api\User\Collection\UserCollection;
use Api\User\Entity\User;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserDetailRepository;
use Api\User\Repository\UserRepository;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\Mail\Exception\MailException;
use Dot\Mail\Service\MailService;
use Exception;
use Mezzio\Template\TemplateRendererInterface;

use function date;

class UserService implements UserServiceInterface
{
    /**
     * @Inject({
     *     UserRoleServiceInterface::class,
     *     MailService::class,
     *     TemplateRendererInterface::class,
     *     OAuthAccessTokenRepository::class,
     *     OAuthRefreshTokenRepository::class,
     *     UserRepository::class,
     *     UserDetailRepository::class,
     *     "config"
     * })
     */
    public function __construct(
        protected UserRoleServiceInterface $userRoleService,
        protected MailService $mailService,
        protected TemplateRendererInterface $templateRenderer,
        protected OAuthAccessTokenRepository $oAuthAccessTokenRepository,
        protected OAuthRefreshTokenRepository $oAuthRefreshTokenRepository,
        protected UserRepository $userRepository,
        protected UserDetailRepository $userDetailRepository,
        protected array $config = []
    ) {
    }

    /**
     * @throws Exception
     */
    public function activateUser(User $user): User
    {
        return $this->userRepository->saveUser($user->activate());
    }

    /**
     * @throws Exception
     */
    public function createUser(array $data = []): User
    {
        if ($this->exists($data['identity'])) {
            throw new Exception(Message::DUPLICATE_IDENTITY);
        }

        if ($this->emailExists($data['detail']['email'])) {
            throw new Exception(Message::DUPLICATE_EMAIL);
        }

        $detail = (new UserDetail())
            ->setFirstName($data['detail']['firstName'] ?? null)
            ->setLastName($data['detail']['lastName'] ?? null)
            ->setEmail($data['detail']['email']);

        $user = (new User())
            ->setDetail($detail)
            ->setIdentity($data['identity'])
            ->usePassword($data['password'])
            ->setStatus($data['status'] ?? User::STATUS_PENDING);
        $detail->setUser($user);

        if (! empty($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                $role = $this->userRoleService->findOneBy(['uuid' => $roleData['uuid']]);
                if ($role instanceof UserRole) {
                    $user->addRole($role);
                }
            }
        } else {
            $role = $this->userRoleService->findOneBy(['name' => UserRole::ROLE_USER]);
            if ($role instanceof UserRole) {
                $user->addRole($role);
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
     * @throws Exception
     */
    public function deleteUser(User $user): User
    {
        $this->revokeTokens($user);

        return $this->anonymizeUser($user->markAsDeleted());
    }

    /**
     * @throws Exception
     */
    public function anonymizeUser(User $user): User
    {
        $placeholder = $this->getAnonymousPlaceholder();

        $user
            ->setIdentity($placeholder)
            ->getDetail()
                ->setFirstName($placeholder)
                ->setLastName($placeholder)
                ->setEmail($placeholder);

        return $this->userRepository->saveUser($user);
    }

    public function exists(string $identity = ''): bool
    {
        return $this->findOneBy(['identity' => $identity]) instanceof User;
    }

    public function existsOther(string $identity = '', string $uuid = ''): bool
    {
        $user = $this->findOneBy(['identity' => $identity]);
        if (! $user instanceof User) {
            return false;
        }

        return $user->getUuid()->toString() !== $uuid;
    }

    public function emailExists(string $email = ''): bool
    {
        return $this->findByEmail($email) instanceof User;
    }

    public function emailExistsOther(string $email = '', string $uuid = ''): bool
    {
        $user = $this->findByEmail($email);
        if (! $user instanceof User) {
            return false;
        }

        return $user->getUuid()->toString() !== $uuid;
    }

    public function findByResetPasswordHash(?string $hash): ?User
    {
        return $this->userRepository->findByResetPasswordHash($hash);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userDetailRepository->findOneBy(['email' => $email])?->getUser();
    }

    public function findByIdentity(string $identity): ?User
    {
        return $this->findOneBy(['identity' => $identity]);
    }

    public function findOneBy(array $params = []): ?User
    {
        return $this->userRepository->findOneBy($params);
    }

    public function getUsers(array $params = []): UserCollection
    {
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

        return $this->mailService->send()->isValid();
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

        return $this->mailService->send()->isValid();
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

        return $this->mailService->send()->isValid();
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

        return $this->mailService->send()->isValid();
    }

    /**
     * @throws Exception
     */
    public function updateUser(User $user, array $data = []): User
    {
        if (isset($data['identity'])) {
            if ($this->existsOther($data['identity'], $user->getUuid()->toString())) {
                throw new Exception(Message::DUPLICATE_IDENTITY);
            }
        }

        if (isset($data['detail']['email'])) {
            if ($this->emailExistsOther($data['detail']['email'], $user->getUuid()->toString())) {
                throw new Exception(Message::DUPLICATE_EMAIL);
            }
        }

        if (isset($data['password'])) {
            $user->usePassword($data['password']);
        }

        if (isset($data['status'])) {
            $user->setStatus($data['status']);
        }

        if (isset($data['isDeleted'])) {
            $user->setIsDeleted($data['isDeleted']);
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
                $role = $this->userRoleService->findOneBy(['uuid' => $roleData['uuid']]);
                if ($role instanceof UserRole) {
                    $user->addRole($role);
                }
            }
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

        return $this->mailService->send()->isValid();
    }

    private function getAnonymousPlaceholder(): string
    {
        return 'anonymous' . date('dmYHis');
    }
}
