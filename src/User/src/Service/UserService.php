<?php

declare(strict_types=1);

namespace Api\User\Service;

use Dot\AnnotatedServices\Annotation\Inject;
use Api\App\Common\Message;
use Api\App\Common\UuidOrderedTimeGenerator;
use Api\User\Collection\UserCollection;
use Api\User\Entity\UserAvatar;
use Api\User\Entity\UserDetail;
use Api\User\Entity\User;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserRepository;
use Doctrine\ORM;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Dot\Mail\Service\MailService;
use Dot\Mail\Exception\MailException;
use Exception;
use Laminas\Diactoros\UploadedFile;
use Mezzio\Template\TemplateRendererInterface;

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
    const EXTENSIONS = [
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/png' => 'png'
    ];

    /** @var array $config */
    protected $config;

    /** @var MailService $mailService */
    protected $mailService;

    /** @var TemplateRendererInterface $templateRenderer */
    protected $templateRenderer;

    /** @var UserRepository $userRepository */
    protected $userRepository;

    /** @var UserRoleService $userRoleService */
    protected $userRoleService;

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
    public function activateUser(User $user)
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
    public function createUser(array $data = [])
    {
        if ($this->exists($data['email'])) {
            throw new ORMException(Message::DUPLICATE_EMAIL);
        }

        $user = new User();
        $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT))->setEmail($data['email']);

        $detail = new UserDetail();
        $detail->setUser($user)->setFirstname($data['detail']['firstname'])->setLastname($data['detail']['lastname']);

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
    public function deleteAvatarFile(string $path)
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
     * @return User|null
     * @throws ORMException
     * @throws ORM\OptimisticLockException
     */
    public function deleteUser(User $user)
    {
        $user = $user->markAsDeleted();

        // make user anonymous
        $user->setEmail('anonymous' . date('dmYHis') . '@dotkernel.com');
        $userDetails = $user->getDetail();
        $userDetails->setFirstName('anonymous' . date('dmYHis'));
        $userDetails->setLastName('anonymous' . date('dmYHis'));

        $user->setDetail($userDetails);

        $this->userRepository->saveUser($user->markAsDeleted());
        $this->userRepository->revokeAccessTokens($user->getEmail());

        return $user;
    }

    /**
     * @param string $email
     * @param null|string $uuid
     * @return bool
     */
    public function exists(string $email = '', ?string $uuid = '')
    {
        return !is_null(
            $this->userRepository->exists($email, $uuid)
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
     * @param array $params
     * @return User|null
     */
    public function findOneBy(array $params = []): ?User
    {
        if (empty($params)) {
            return null;
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy($params);

        return $user;
    }

    /**
     * @param array $params
     * @return UserCollection
     */
    public function getUsers(array $params = [])
    {
        return $this->userRepository->getUsers($params);
    }

    /**
     * @param User $user
     * @return bool
     * @throws MailException
     */
    public function sendActivationMail(User $user)
    {
        if ($user->isActive()) {
            return false;
        }

        $this->mailService->setBody(
            $this->templateRenderer->render('user::activate', [
                'config' => $this->config,
                'user' => $user
            ])
        );
        $this->mailService->setSubject('Welcome to ' . $this->config['application']['name']);
        $this->mailService->getMessage()->addTo($user->getEmail(), $user->getName());

        return $this->mailService->send()->isValid();
    }

    /**
     * @param User $user
     * @return bool
     * @throws MailException
     */
    public function sendResetPasswordRequestedMail(User $user)
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
        $this->mailService->getMessage()->addTo($user->getEmail(), $user->getName());

        return $this->mailService->send()->isValid();
    }

    /**
     * @param User $user
     * @return bool
     * @throws MailException
     */
    public function sendResetPasswordCompletedMail(User $user)
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
        $this->mailService->getMessage()->addTo($user->getEmail(), $user->getName());

        return $this->mailService->send()->isValid();
    }

    /**
     * @param User $user
     * @return bool
     * @throws MailException
     */
    public function sendWelcomeMail(User $user)
    {
        $this->mailService->setBody(
            $this->templateRenderer->render('user::welcome', [
                'config' => $this->config,
                'user' => $user
            ])
        );
        $this->mailService->setSubject('Welcome to ' . $this->config['application']['name']);
        $this->mailService->getMessage()->addTo($user->getEmail(), $user->getName());

        return $this->mailService->send()->isValid();
    }

    /**
     * @param User $user
     * @param array $data
     * @return User
     * @throws Exception
     * @throws ORMException
     */
    public function updateUser(User $user, array $data = [])
    {
        if (isset($data['email']) && !is_null($data['email'])) {
            if ($this->exists($data['email'], $user->getUuid()->toString())) {
                throw new ORMException(Message::DUPLICATE_EMAIL);
            }
            $user->setEmail($data['email']);
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
    protected function createAvatar(User $user, UploadedFile $uploadedFile)
    {
        $path = $this->config['uploads']['user']['path'] . DIRECTORY_SEPARATOR;
        $path .= $user->getUuid()->toString() . DIRECTORY_SEPARATOR;
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
        $fileName = sprintf('avatar-%s.%s',
            UuidOrderedTimeGenerator::generateUuid(),
            self::EXTENSIONS[$uploadedFile->getClientMediaType()]
        );
        $avatar->setName($fileName);

        $uploadedFile = new UploadedFile(
            $uploadedFile->getStream()->getMetadata()['uri'], $uploadedFile->getSize(), $uploadedFile->getError()
        );
        $uploadedFile->moveTo($path . $fileName);

        return $avatar;
    }
}
