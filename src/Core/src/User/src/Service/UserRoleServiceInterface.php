<?php

declare(strict_types=1);

namespace Core\User\Service;

use Core\App\Exception\NotFoundException;
use Core\User\Entity\UserRole;
use Core\User\Repository\UserRoleRepository;

interface UserRoleServiceInterface
{
    public function getUserRoleRepository(): UserRoleRepository;

    /**
     * @throws NotFoundException
     */
    public function find(string $id): UserRole;
}
