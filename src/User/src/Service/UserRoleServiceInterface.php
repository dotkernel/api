<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\NotFoundException;
use Core\User\Entity\UserRole;
use Core\User\Repository\UserRoleRepository;
use Doctrine\ORM\QueryBuilder;

interface UserRoleServiceInterface
{
    public function getUserRoleRepository(): UserRoleRepository;

    /**
     * @throws NotFoundException
     */
    public function findUserRole(string $id): UserRole;

    /**
     * @param array<non-empty-string, mixed> $params
     */
    public function getUserRoles(array $params): QueryBuilder;
}
