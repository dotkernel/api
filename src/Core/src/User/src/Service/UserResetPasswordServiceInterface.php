<?php

declare(strict_types=1);

namespace Core\User\Service;

use Core\App\Exception\NotFoundException;
use Core\User\Entity\UserResetPassword;
use Core\User\Repository\UserResetPasswordRepository;

interface UserResetPasswordServiceInterface
{
    public function getUserResetPasswordRepository(): UserResetPasswordRepository;

    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params): UserResetPassword;
}
