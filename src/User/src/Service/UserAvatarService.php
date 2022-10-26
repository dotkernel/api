<?php

namespace Api\User\Service;

use Api\App\Entity\UuidOrderedTimeGenerator;
use Api\User\Entity\User;
use Api\User\Entity\UserAvatar;
use Api\User\Repository\UserAvatarRepository;
use Dot\AnnotatedServices\Annotation\Inject;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class UserAvatarService
 * @package Api\User\Service
 */
class UserAvatarService
{
    public const EXTENSIONS = [
        'image/jpg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/png' => 'png'
    ];

    protected UserAvatarRepository $userAvatarRepository;

    protected array $config;

    /**
     * UserAvatarService constructor.
     * @param UserAvatarRepository $userAvatarRepository
     * @param array $config
     *
     * @Inject({UserAvatarRepository::class, "config"})
     */
    public function __construct(UserAvatarRepository $userAvatarRepository, array $config)
    {
        $this->userAvatarRepository = $userAvatarRepository;
        $this->config = $config;
    }

    /**
     * @param User $user
     * @param UploadedFile $uploadedFile
     * @return UserAvatar
     */
    public function createAvatar(User $user, UploadedFile $uploadedFile): UserAvatar
    {
        $path = $this->getUserAvatarDirectoryPath($user);

        $this->ensureDirectoryExists($path);

        if ($user->getAvatar() instanceof UserAvatar) {
            $avatar = $user->getAvatar();
            $this->deleteAvatarFile($path . $avatar->getName());
        } else {
            $avatar = new UserAvatar();
            $avatar->setUser($user);
        }

        $fileName = $this->createFileName($uploadedFile->getClientMediaType());

        $avatar->setName($fileName);

        $this->saveAvatarImage($uploadedFile, $path . $fileName);
        $this->userAvatarRepository->saveAvatar($avatar);

        return $avatar;
    }

    /**
     * @param User $user
     * @return bool
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
     * @return string
     */
    protected function getUserAvatarDirectoryPath(User $user): string
    {
        return sprintf('%s/%s/', $this->config['uploads']['user']['path'], $user->getUuid()->toString());
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function deleteAvatarFile(string $path): bool
    {
        if (empty($path) || ! is_readable($path)) {
            return false;
        }

        return unlink($path);
    }

    /**
     * @param string $path
     * @return void
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (!file_exists($path)) {
            mkdir($path, 0755);
        }
    }

    /**
     * @param string $fileType
     * @return string
     */
    protected function createFileName(string $fileType): string
    {
        return sprintf(
            'avatar-%s.%s',
            UuidOrderedTimeGenerator::generateUuid()->toString(),
            self::EXTENSIONS[$fileType]
        );
    }

    protected function saveAvatarImage(UploadedFileInterface $uploadedFile, string $location): void
    {
        $uploadedFile->moveTo($location);
    }
}
