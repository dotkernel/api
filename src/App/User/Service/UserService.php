<?php

declare(strict_types=1);

namespace App\User\Service;

use App\Common\UuidOrderedTimeGenerator;
use App\User\Entity\UserAvatarEntity;
use App\User\Entity\UserDetailEntity;
use App\User\Entity\UserEntity;
use App\User\Entity\UserRoleEntity;
use App\User\Repository\UserRepository;
use Doctrine\ORM\ORMException;
use Zend\Diactoros\UploadedFile;

use function file_exists;
use function is_null;
use function in_array;
use function mkdir;
use function password_hash;
use function sprintf;

/**
 * Class UserService
 * @package App\User\Service
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

    /** @var UserRepository $userRepository */
    protected $userRepository;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     * @param array $config
     */
    public function __construct(UserRepository $userRepository, array $config = [])
    {
        $this->userRepository = $userRepository;
        $this->config = $config;
    }

    /**
     * @return UserRepository
     */
    public function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    /**
     * @param array $data
     * @param UserRoleEntity $role
     * @return UserEntity
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createUser(array $data = [], UserRoleEntity $role)
    {
        if ($this->exists($data['email'])) {
            throw new ORMException('An account with this email address already exists.');
        }

        $detail = new UserDetailEntity();
        $detail->setFirstname($data['firstname'])->setLastname($data['lastname']);

        $user = new UserEntity();
        $user->setEmail($data['email'])->setDetail($detail)->setRoles([$role])->setPassword(
            password_hash($data['password'], PASSWORD_DEFAULT)
        );

        $this->getUserRepository()->saveUser($user);

        return $user;
    }

    /**
     * @param UserEntity $user
     * @return bool
     */
    public function deleteAvatar(UserEntity $user)
    {
        $config = $this->config['uploads']['user'];
        $path = sprintf('%s/%s/%s',
            $config['path'],
            $user->getUuid()->toString(),
            $user->getAvatar()->getName()
        );

        if (is_readable($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * @param string $identity
     * @param null|string $uuid
     * @return bool
     */
    public function exists(string $identity = '', ?string $uuid = '')
    {
        return !is_null(
            $this->getUserRepository()->getUser($identity, $uuid)
        );
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getRole(string $name = '')
    {
        return $this->getUserRepository()->getRole($name);
    }

    /**
     * @param string $identifier
     * @return null|UserEntity
     */
    public function identify(string $identifier = '')
    {
        /** @var UserEntity $user */
        $user = $this->getUserRepository()->findOneBy([
            'email' => $identifier
        ]);

        return $user;
    }

    /**
     * @param UserEntity $user
     * @param array $data
     * @return UserEntity
     * @throws ORMException
     */
    public function updateUser(UserEntity $user, array $data = [])
    {
        if (!empty($data['email']) && $this->exists($data['email'], $user->getUuid()->toString())) {
            throw new ORMException('An account with this email address already exists.');
        }

        if (!empty($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (!empty($data['password'])) {
            $user->setPassword(
                password_hash($data['password'], PASSWORD_DEFAULT)
            );
        }
        if (!empty($data['status']) && in_array($data['status'], $user->getStatuses())) {
            $user->setEmail($data['status']);
        }
        if (!empty($data['firstname'])) {
            $user->getDetail()->setFirstname($data['firstname']);
        }
        if (!empty($data['lastname'])) {
            $user->getDetail()->setLastname($data['lastname']);
        }
        if (!empty($data['avatar'])) {
            if ($name = $this->uploadAvatar($user, $data['avatar'])) {
                if (is_null($user->getAvatar())) {
                    $avatar = new UserAvatarEntity();
                    $avatar->setName($name);
                    $user->setAvatar($avatar);
                } else {
                    $this->deleteAvatar($user);
                    $user->getAvatar()->setName($name);
                }
            }
        }

        $this->getUserRepository()->saveUser($user);

        return $user;
    }

    /**
     * @param UserEntity $user
     * @param array $avatar
     * @return string
     */
    protected function uploadAvatar(UserEntity $user, array $avatar = [])
    {
        $config = $this->config['uploads']['user'];
        $path = sprintf('%s/%s', $config['path'], $user->getUuid()->toString());
        if (!file_exists($path)) {
            mkdir($path, 0755);
        }

        $fileName = sprintf('avatar-%s.%s',
            UuidOrderedTimeGenerator::generateUuid(),
            $this->extensions[$avatar['type']]
        );

        $uploadedFile = new UploadedFile($avatar['tmp_name'], $avatar['size'], $avatar['error']);
        $uploadedFile->moveTo(sprintf('%s/%s', $path, $fileName));

        return $fileName;
    }
}
