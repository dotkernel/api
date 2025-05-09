<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\NotFoundException;
use Core\User\Entity\User;
use Core\User\Entity\UserResetPassword;
use Core\User\Repository\UserResetPasswordRepository;

interface UserResetPasswordServiceInterface
{
    public function getUserResetPasswordRepository(): UserResetPasswordRepository;

    /**
     * @param array<string, mixed> $params
     * @throws NotFoundException
     */
    public function findOneBy(array $params): UserResetPassword;

    public function saveResetPassword(User $user): UserResetPassword;
}
