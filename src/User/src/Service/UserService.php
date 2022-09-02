<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Entity\UuidOrderedTimeGenerator;
use Api\App\Message;
use Api\User\Collection\UserCollection;
use Api\User\Entity\UserAvatar;
use Api\User\Entity\UserDetail;
use Api\User\Entity\User;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserAvatarRepository;
use Api\User\Repository\UserDetailRepository;
use Api\User\Repository\UserRepository;
use Doctrine\ORM;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\Mail\Exception\MailException;
use Dot\Mail\Service\MailService;
use Exception;
use Laminas\Diactoros\UploadedFile;
use Mezzio\Template\TemplateRendererInterface;
use Throwable;

use function file_exists;
use function is_null;
use function is_readable;
use function mkdir;
use function password_hash;
use function sprintf;
use function unlink;

/**
 * Class UserService
 * @package Api\User\Service
 */
class UserService
{
    public const EXTENSIONS = [
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/png' => 'png'
    ];

    protected array $config;

    protected MailService $mailService;

    protected TemplateRendererInterface $templateRenderer;

    protected UserRepository $userRepository;

    protected UserAvatarRepository $userAvatarRepository;

    protected UserDetailRepository $userDetailRepository;

    protected UserRoleService $userRoleService;

    /**
     * UserService constructor.
     * @param EntityManager $entityManager
     * @param UserRoleService $userRoleService
     * @param MailService $mailService
     * @param TemplateRendererInterface $templateRenderer
     * @param array $config
     *
     * @Inject({EntityManager::class, UserRoleService::class, MailService::class, TemplateRendererInterface::class,
     *     "config"})
     */
    public function __construct(
        EntityManager $entityManager,
        UserRoleService $userRoleService,
        MailService $mailService,
        TemplateRendererInterface $templateRenderer,
        array $config = []
    ) {
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->userAvatarRepository = $entityManager->getRepository(UserAvatar::class);
        $this->userDetailRepository = $entityManager->getRepository(UserDetail::class);
        $this->userRoleService = $userRoleService;
        $this->mailService = $mailService;
        $this->templateRenderer = $templateRenderer;
        $this->config = $config;
    }

    /**
     * @param User $user
     * @return User
     * @throws ORMException
     * @throws ORM\OptimisticLockException
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
     * @throws ORM\OptimisticLockException
     */
    public function createUser(array $data = []): User
    {
        if ($this->exists($data['identity'])) {
            throw new ORMException(Message::DUPLICATE_IDENTITY);
        }
        $user = new User();
        $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT))->setIdentity($data['identity']);

        $detail = new UserDetail();
        $detail->setUser($user)->setFirstname($data['detail']['firstname'])->setLastname($data['detail']['lastname']);

        if (!empty($data['detail']['email']) && !$this->emailExists($data['detail']['email'])) {
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
     * @param string $path
     * @return bool
     */
    public function deleteAvatarFile(string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        if (is_readable($path)) {
            return unlink($path);
        }

        return false;
    }

    /**
     * @param User $user
     * @return User
     * @throws ORMException
     * @throws ORM\OptimisticLockException
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
     * @param User $user
     * @return string
     */
    public function getUserAvatarDirectoryPath(User $user): string
    {
        return sprintf('%s/%s/', $this->config['uploads']['user']['path'], $user->getUuid()->toString());
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
     * @throws ORM\OptimisticLockException
     * @throws Throwable
     */
    public function updateUser(User $user, array $data = []): User
    {
        if (isset($data['identity']) && !is_null($data['identity'])) {
            if ($this->exists($data['identity'], $user->getUuid()->toString())) {
                throw new ORMException(Message::DUPLICATE_IDENTITY);
            }
            $user->setIdentity($data['identity']);
        }

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

        if (isset($data['detail']['firstname']) && !is_null($data['detail']['firstname'])) {
            $user->getDetail()->setFirstname($data['detail']['firstname']);
        }

        if (isset($data['detail']['lastname']) && !is_null($data['detail']['lastname'])) {
            $user->getDetail()->setLastname($data['detail']['lastname']);
        }

        if (isset($data['detail']['email']) && !empty($data['detail']['email'])) {
            if ($this->emailExists($data['detail']['email'], $user->getUuid()->toString())) {
                throw new ORMException(Message::DUPLICATE_EMAIL);
            }
            $user->getDetail()->setEmail($data['detail']['email']);
        }

        if (!empty($data['avatar'])) {
            $user->setAvatar(
                $this->createAvatar($user, $data['avatar'])
            );
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
     * @param UploadedFile $uploadedFile
     * @return UserAvatar
     */
    protected function createAvatar(User $user, UploadedFile $uploadedFile): UserAvatar
    {
        $path = $this->getUserAvatarDirectoryPath($user);
        if (!file_exists($path)) {
            mkdir($path, 0755);
        }

        if ($user->getAvatar() instanceof UserAvatar) {
            $avatar = $user->getAvatar();
            $this->deleteAvatarFile($path . $avatar->getName());
        } else {
            $avatar = new UserAvatar();
            $avatar->setUser($user);
        }
        $fileName = sprintf(
            'avatar-%s.%s',
            UuidOrderedTimeGenerator::generateUuid()->toString(),
            self::EXTENSIONS[$uploadedFile->getClientMediaType()]
        );
        $avatar->setName($fileName);

        $uploadedFile = new UploadedFile(
            $uploadedFile->getStream()->getMetadata()['uri'],
            $uploadedFile->getSize(),
            $uploadedFile->getError()
        );
        $uploadedFile->moveTo($path . $fileName);

        return $avatar;
    }

    /**
     * @param User $user
     * @return bool
     * @throws ORMException
     * @throws ORM\OptimisticLockException
     */
    public function removeAvatar(User $user): bool
    {
        if (!($user->getAvatar() instanceof UserAvatar)) {
            return false;
        }
        $path = $this->getUserAvatarDirectoryPath($user);
        $this->userAvatarRepository->deleteAvatar($user->getAvatar());
        return $this->deleteAvatarFile($path . $user->getAvatar()->getName());
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
