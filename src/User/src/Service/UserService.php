<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Message;
use Api\User\Collection\UserCollection;
use Api\User\Entity\UserDetail;
use Api\User\Entity\User;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserDetailRepository;
use Api\User\Repository\UserRepository;
use Doctrine\ORM\ORMException;
use Dot\Mail\Exception\MailException;
use Dot\Mail\Service\MailService;
use Mezzio\Template\TemplateRendererInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Doctrine\ORM;
use Exception;
use Throwable;

use function is_null;
use function password_hash;

/**
 * Class UserService
 * @package Api\User\Service
 */
class UserService
{
    protected UserRoleService $userRoleService;

    protected MailService $mailService;

    protected TemplateRendererInterface $templateRenderer;

    protected UserRepository $userRepository;

    protected UserDetailRepository $userDetailRepository;

    protected array $config;

    /**
     * UserService constructor.
     * @param UserRoleService $userRoleService
     * @param MailService $mailService
     * @param TemplateRendererInterface $templateRenderer
     * @param UserRepository $userRepository
     * @param UserDetailRepository $userDetailRepository
     * @param array $config
     *
     * @Inject({
     *     UserRoleService::class,
     *     MailService::class,
     *     TemplateRendererInterface::class,
     *     UserRepository::class,
     *     UserDetailRepository::class,
     *     "config"
     * })
     */
    public function __construct(
        UserRoleService $userRoleService,
        MailService $mailService,
        TemplateRendererInterface $templateRenderer,
        UserRepository $userRepository,
        UserDetailRepository $userDetailRepository,
        array $config = []
    ) {
        $this->userRoleService = $userRoleService;
        $this->mailService = $mailService;
        $this->templateRenderer = $templateRenderer;
        $this->userRepository = $userRepository;
        $this->userDetailRepository = $userDetailRepository;
        $this->config = $config;
    }

    /**
     * @param User $user
     * @return User
     */
    public function activateUser(User $user): User
    {
        $this->userRepository->saveUser($user->activate());

        return $user;
    }

    /**
     * @param array $data
     * @return User
     * @throws Exception
     * @throws ORMException
     */
    public function createUser(array $data = []): User
    {
        if ($this->exists($data['identity'])) {
            throw new ORMException(Message::DUPLICATE_IDENTITY);
        }
        if (! empty($data['detail']['email']) && $this->emailExists($data['detail']['email'])) {
            throw new ORMException(Message::DUPLICATE_EMAIL);
        }

        $user = new User();
        $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT))->setIdentity($data['identity']);

        $detail = new UserDetail();
        $detail->setUser($user);

        if (!empty($data['detail']['firstName'])) {
            $detail->setFirstName($data['detail']['firstName']);
        }

        if (!empty($data['detail']['lastName'])) {
            $detail->setLastName($data['detail']['lastName']);
        }

        if (!empty($data['detail']['email'])) {
            $detail->setEmail($data['detail']['email']);
        }

        $user->setDetail($detail);

        if (!empty($data['status'])) {
            $user->setStatus($data['status']);
        }

        if (!empty($data['roles'])) {
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
        if ($user->getRoles()->count() === 0) {
            throw new Exception(Message::RESTRICTION_ROLES);
        }

        $this->userRepository->saveUser($user);

        return $user;
    }

    /**
     * @param User $user
     * @return User
     */
    public function deleteUser(User $user): User
    {
        $placeholder = $this->getAnonymousPlaceholder();

        // make user anonymous
        $userDetail = $user->getDetail();
        $userDetail
            ->setFirstName($placeholder)
            ->setLastName($placeholder)
            ->setEmail($placeholder);

        $user
            ->markAsDeleted()
            ->setDetail($userDetail)
            ->setIdentity($placeholder);

        $this->userRepository->saveUser($user);
        $this->userRepository->revokeAccessTokens($user->getIdentity());

        return $user;
    }

    /**
     * @return string
     */
    private function getAnonymousPlaceholder(): string
    {
        return 'anonymous' . date('dmYHis');
    }

    /**
     * @param string $identity
     * @param string|null $uuid
     * @return bool
     */
    public function exists(string $identity = '', ?string $uuid = ''): bool
    {
        return !empty(
            $this->userRepository->exists($identity, $uuid)
        );
    }

    /**
     * @param string $email
     * @param string|null $uuid
     * @return bool
     */
    public function emailExists(string $email = '', ?string $uuid = ''): bool
    {
        return !empty(
            $this->userRepository->emailExists($email, $uuid)
        );
    }

    /**
     * @param string|null $hash
     * @return User|null
     */
    public function findByResetPasswordHash(?string $hash): ?User
    {
        if (empty($hash)) {
            return null;
        }

        return $this->userRepository->findByResetPasswordHash($hash);
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        $detail = $this->userDetailRepository->findOneBy([
            'email' => $email
        ]);
        if ($detail instanceof UserDetail) {
            return $detail->getUser();
        }
        return null;
    }

    /**
     * @param string $identity
     * @return User|null
     */
    public function findByIdentity(string $identity): ?User
    {
        return $this->userRepository->findOneBy([
            'identity' => $identity
        ]);
    }

    /**
     * @param array $params
     * @return User|null
     */
    public function findOneBy(array $params = []): ?User
    {
        if (empty($params)) {
            return null;
        }

        return $this->userRepository->findOneBy($params);
    }

    /**
     * @param array $params
     * @return UserCollection
     */
    public function getUsers(array $params = []): UserCollection
    {
        return $this->userRepository->getUsers($params);
    }

    /**
     * @param User $user
     * @return bool
     * @throws MailException
     */
    public function sendActivationMail(User $user): bool
    {
        if ($user->isActive() || is_null($user->getDetail()->getEmail())) {
            return false;
        }

        $this->mailService->setBody(
            $this->templateRenderer->render('user::activate', [
                'config' => $this->config,
                'user' => $user
            ])
        );
        $this->mailService->setSubject('Welcome to ' . $this->config['application']['name']);
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());

        return $this->mailService->send()->isValid();
    }

    /**
     * @param User $user
     * @return bool
     * @throws MailException
     */
    public function sendResetPasswordRequestedMail(User $user): bool
    {
        $this->mailService->setBody(
            $this->templateRenderer->render('user::reset-password-requested', [
                'config' => $this->config,
                'user' => $user
            ])
        );

        $this->mailService->setSubject(
            'Reset password instructions for your ' . $this->config['application']['name'] . ' account'
        );
        if (!empty($user->getDetail()->getEmail())) {
            $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
        } else {
            return false;
        }

        return $this->mailService->send()->isValid();
    }

    /**
     * @param User $user
     * @return bool
     * @throws MailException
     */
    public function sendResetPasswordCompletedMail(User $user): bool
    {
        $this->mailService->setBody(
            $this->templateRenderer->render('user::reset-password-completed', [
                'config' => $this->config,
                'user' => $user
            ])
        );
        $this->mailService->setSubject(
            'You have successfully reset the password for your ' . $this->config['application']['name'] . ' account'
        );
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());

        return $this->mailService->send()->isValid();
    }

    /**
     * @param User $user
     * @return bool
     * @throws MailException
     */
    public function sendWelcomeMail(User $user): bool
    {
        $this->mailService->setBody(
            $this->templateRenderer->render('user::welcome', [
                'config' => $this->config,
                'user' => $user
            ])
        );
        $this->mailService->setSubject('Welcome to ' . $this->config['application']['name']);
        $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());

        return $this->mailService->send()->isValid();
    }

    /**
     * @param User $user
     * @param array $data
     * @return User
     * @throws ORMException
     * @throws Throwable
     */
    public function updateUser(User $user, array $data = []): User
    {
        if (isset($data['password']) && !is_null($data['password'])) {
            $user->setPassword(
                password_hash($data['password'], PASSWORD_DEFAULT)
            );
        }

        if (isset($data['status']) && !empty($data['status'])) {
            $user->setStatus($data['status']);
        }

        if (isset($data['isDeleted']) && !is_null($data['isDeleted'])) {
            $user->setIsDeleted($data['isDeleted']);
        }

        if (isset($data['hash']) && !empty($data['hash'])) {
            $user->setHash($data['hash']);
        }

        if (isset($data['detail']['firstName']) && !is_null($data['detail']['firstName'])) {
            $user->getDetail()->setFirstname($data['detail']['firstName']);
        }

        if (isset($data['detail']['lastName']) && !is_null($data['detail']['lastName'])) {
            $user->getDetail()->setLastName($data['detail']['lastName']);
        }

        if (isset($data['detail']['email']) && !empty($data['detail']['email'])) {
            if ($this->emailExists($data['detail']['email'], $user->getUuid()->toString())) {
                throw new ORMException(Message::DUPLICATE_EMAIL);
            }
            $user->getDetail()->setEmail($data['detail']['email']);
        }

        if (!empty($data['roles'])) {
            $user->resetRoles();
            foreach ($data['roles'] as $roleData) {
                $role = $this->userRoleService->findOneBy(['uuid' => $roleData['uuid']]);
                if ($role instanceof UserRole) {
                    $user->addRole($role);
                }
            }
        }
        if ($user->getRoles()->count() === 0) {
            throw new Exception(Message::RESTRICTION_ROLES);
        }

        $this->userRepository->saveUser($user);

        return $user;
    }

    /**
     * @param User $user
     * @return bool
     * @throws MailException
     */
    public function sendRecoverIdentityMail(User $user): bool
    {
        $this->mailService->setBody(
            $this->templateRenderer->render('user::recover-identity-requested', [
                'config' => $this->config,
                'user' => $user
            ])
        );

        $this->mailService->setSubject(
            'Recover identity for your ' . $this->config['application']['name'] . ' account'
        );

        if (!empty($user->getDetail()->getEmail())) {
            $this->mailService->getMessage()->addTo($user->getDetail()->getEmail(), $user->getName());
            return $this->mailService->send()->isValid();
        }

        return false;
    }
}
