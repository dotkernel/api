<?php

declare(strict_types=1);

namespace Api\User\Service;

use Core\User\Entity\User;
use Core\User\Entity\UserAvatar;
use Core\User\Repository\UserAvatarRepository;
use Laminas\Diactoros\UploadedFile;

interface UserAvatarServiceInterface
{
    public function getUserAvatarRepository(): UserAvatarRepository;

    public function deleteAvatar(User $user): void;

    public function saveAvatar(User $user, UploadedFile $uploadedFile): UserAvatar;
}
