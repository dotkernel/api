<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Common\Message;
use Api\App\Common\UuidOrderedTimeGenerator;
use Api\User\Collection\UserCollection;
use Api\User\Entity\UserAvatarEntity;
use Api\User\Entity\UserDetailEntity;
use Api\User\Entity\UserEntity;
use Api\User\Entity\UserRoleEntity;
use Api\User\Repository\UserRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM;
use Dot\Mail\Service\MailService;
use Dot\Mail\Exception\MailException;
use Exception;
use Zend\Diactoros\UploadedFile;
use Zend\Expressive\Template\TemplateRendererInterface;

use function file_exists;
use function is_null;
use function is_readable;
use function mkdir;
use function password_hash;
use function sprintf;
use function unlink;

/**
 * Class UserService
 * @package Api\App\User\Service
 */
class UserService
{
    protected $extensions = [
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
     * @param UserRepository $userRepository
     * @param UserRoleService $userRoleService
     * @param MailService $mailService
     * @param TemplateRendererInterface $templateRenderer
     * @param array $config
     */
    public function __construct(
        UserRepository $userRepository,
        UserRoleService $userRoleService,
        MailService $mailService,
        TemplateRendererInterface $templateRenderer,
        array $config = []
    ) {
        $this->userRepository = $userRepository;
        $this->userRoleService = $userRoleService;
        $this->mailService = $mailService;
        $this->templateRenderer = $templateRenderer;
        $this->config = $config;
    }

    /**
     * @param UserEntity $user
     * @return UserEntity
     * @throws ORMException
     * @throws ORM\OptimisticLockException
     */
    public function activateUser(UserEntity $user)
    {
        $this->userRepository->saveUser($user->activate());

        return $user;
    }

    /**
     * @param array $data
     * @return UserEntity
     * @throws Exception
     * @throws ORMException
     * @throws ORM\OptimisticLockException
     */
    public function createUser(array $data = [])
    {
        if ($this->exists($data['email'])) {
            throw new ORMException(Message::DUPLICATE_EMAIL);
        }

        $detail = new UserDetailEntity();
        $detail->exchangeArray($data['detail']);

        $user = new UserEntity();
        $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT))
            ->setDetail($detail)
            ->setEmail($data['email']);

        if (!empty($data['status'])) {
            $user->setStatus($data['status']);
        }

        if (!empty($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                $role = $this->userRoleService->findOneBy(['uuid' => $roleData['uuid']]);
                if ($role instanceof UserRoleEntity) {
                    $user->addRole($role);
                }
            }
        } else {
            $role = $this->userRoleService->findOneBy(['name' => UserRoleEntity::ROLE_MEMBER]);
            if ($role instanceof UserRoleEntity) {
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
     * @param UserEntity $user
     * @param array $params
     * @return UserEntity|null
     * @throws ORMException
     * @throws ORM\OptimisticLockException
     */
    public function deleteUser(UserEntity $user, array $params = [])
    {
        $email = $user->getEmail();
        if (!empty($params['delete']) && $params['delete'] === 'true') {
            $user = $this->userRepository->deleteUser($user);
            $this->userRepository->deleteAccessTokens($email);
        } else {
            $user = $this->markAsDeleted($user);
            $this->userRepository->revokeAccessTokens($email);
        }

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
     * @return UserEntity|null
     */
    public function findByResetPasswordHash(?string $hash): ?UserEntity
    {
        if (empty($hash)) {
            return null;
        }

        return $this->userRepository->findByResetPasswordHash($hash);
    }

    /**
     * @param array $params
     * @return UserEntity|null
     */
    public function findOneBy(array $params = []): ?UserEntity
    {
        if (empty($params)) {
            return null;
        }

        /** @var UserEntity $user */
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
     * @param UserEntity $user
     * @return bool
     * @throws MailException
     */
    public function sendActivationMail(UserEntity $user)
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
     * @param UserEntity $user
     * @return bool
     * @throws MailException
     */
    public function sendResetPasswordRequestedMail(UserEntity $user)
    {
        $this->mailService->setBody(
            $this->templateRenderer->render('user::reset-password-requested', [
                'config' => $this->config,
                'user' => $user
            ])
        );
        $this->mailService->setSubject('Reset your ' . $this->config['application']['name'] . ' password');
        $this->mailService->getMessage()->addTo($user->getEmail(), $user->getName());

        return $this->mailService->send()->isValid();
    }

    /**
     * @param UserEntity $user
     * @return bool
     * @throws MailException
     */
    public function sendResetPasswordCompletedMail(UserEntity $user)
    {
        $this->mailService->setBody(
            $this->templateRenderer->render('user::reset-password-completed', [
                'config' => $this->config,
                'user' => $user
            ])
        );
        $this->mailService->setSubject('Reset your ' . $this->config['application']['name'] . ' password');
        $this->mailService->getMessage()->addTo($user->getEmail(), $user->getName());

        return $this->mailService->send()->isValid();
    }

    /**
     * @param UserEntity $user
     * @return UserEntity
     * @throws ORMException
     * @throws ORM\OptimisticLockException
     */
    public function markAsDeleted(UserEntity $user)
    {
        $this->userRepository->saveUser($user->markAsDeleted());

        return $user;
    }

    /**
     * @param UserEntity $user
     * @param array $data
     * @return UserEntity
     * @throws Exception
     * @throws ORMException
     */
    public function updateUser(UserEntity $user, array $data = [])
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
                if ($role instanceof UserRoleEntity) {
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
     * @param UserEntity $user
     * @param UploadedFile $uploadedFile
     * @return UserAvatarEntity
     */
    protected function createAvatar(UserEntity $user, UploadedFile $uploadedFile)
    {
        $path = $this->config['uploads']['user']['path'] . DIRECTORY_SEPARATOR;
        $path .= $user->getUuid()->toString() . DIRECTORY_SEPARATOR;
        if (!file_exists($path)) {
            mkdir($path, 0755);
        }

        if ($user->getAvatar() instanceof UserAvatarEntity) {
            $avatar = $user->getAvatar();
            $this->deleteAvatarFile($path . $avatar->getName());
        } else {
            $avatar = new UserAvatarEntity();
        }
        $fileName = sprintf('avatar-%s.%s',
            UuidOrderedTimeGenerator::generateUuid(),
            $this->extensions[$uploadedFile->getClientMediaType()]
        );
        $avatar->setName($fileName);

        $uploadedFile = new UploadedFile(
            $uploadedFile->getStream()->getMetadata()['uri'], $uploadedFile->getSize(), $uploadedFile->getError()
        );
        $uploadedFile->moveTo($path . $fileName);

        return $avatar;
    }
}
