<?php

declare(strict_types=1);

namespace Api\User\Service;

use Core\User\Entity\User;
use Core\User\Entity\UserAvatar;
use Laminas\Diactoros\UploadedFile;

interface UserAvatarServiceInterface
{
    public function createAvatar(User $user, UploadedFile $uploadedFile): UserAvatar;

    public function removeAvatar(User $user): void;
}
