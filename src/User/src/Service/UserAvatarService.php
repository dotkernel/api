<?php

declare(strict_types=1);

namespace Api\User\Service;

use Core\User\Entity\User;
use Core\User\Entity\UserAvatar;
use Core\User\Repository\UserAvatarRepository;
use Dot\DependencyInjection\Attribute\Inject;
use FilesystemIterator;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;
use Ramsey\Uuid\Uuid;

use function assert;
use function file_exists;
use function is_readable;
use function mkdir;
use function rmdir;
use function rtrim;
use function sprintf;
use function unlink;

class UserAvatarService implements UserAvatarServiceInterface
{
    public const EXTENSIONS = [
        'image/jpg'  => 'jpg',
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
    ];

    /**
     * @param array<non-empty-string, mixed> $config
     */
    #[Inject(
        UserAvatarRepository::class,
        'config',
    )]
    public function __construct(
        protected UserAvatarRepository $userAvatarRepository,
        protected array $config,
    ) {
    }

    public function getUserAvatarRepository(): UserAvatarRepository
    {
        return $this->userAvatarRepository;
    }

    public function deleteAvatar(User $user): void
    {
        if (! $user->hasAvatar()) {
            return;
        }

        $avatar = $user->getAvatar();
        assert($avatar instanceof UserAvatar);

        $path = $this->getUserAvatarDirectoryPath($user);
        $this->userAvatarRepository->deleteResource($avatar);
        $this->deleteAvatarFile($path . $avatar->getName());

        if (file_exists($path)) {
            $fsIterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
            if (! $fsIterator->valid()) {
                rmdir($path);
            }
        }
    }

    public function saveAvatar(User $user, UploadedFile $uploadedFile): UserAvatar
    {
        $path = $this->getUserAvatarDirectoryPath($user);

        $this->ensureDirectoryExists($path);

        if ($user->hasAvatar()) {
            $avatar = $user->getAvatar();
            assert($avatar instanceof UserAvatar);
            $this->deleteAvatarFile($path . $avatar->getName());
        } else {
            $avatar = (new UserAvatar())->setUser($user);
        }

        $fileName = $this->createFileName((string) $uploadedFile->getClientMediaType());
        $this->saveAvatarImage($uploadedFile, $path . $fileName);
        $this->userAvatarRepository->saveResource($avatar->setName($fileName));

        return $avatar;
    }

    protected function createFileName(string $fileType): string
    {
        return sprintf(
            'avatar-%s.%s',
            Uuid::uuid7()->toString(),
            self::EXTENSIONS[$fileType]
        );
    }

    protected function deleteAvatarFile(string $path): void
    {
        if (is_readable($path)) {
            unlink($path);
        }
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (! file_exists($path)) {
            mkdir($path, 0755);
        }
    }

    protected function getUserAvatarDirectoryPath(User $user): string
    {
        return sprintf(
            '%s/%s/',
            rtrim($this->config['uploads']['user']['path'], '/'),
            $user->getUuid()->toString()
        );
    }

    protected function saveAvatarImage(UploadedFileInterface $uploadedFile, string $location): void
    {
        $uploadedFile->moveTo($location);
    }
}
