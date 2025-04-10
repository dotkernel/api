<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Core\Admin\Entity\AdminRole;
use Core\Admin\Repository\AdminRoleRepository;
use Core\App\Exception\NotFoundException;
use Doctrine\ORM\QueryBuilder;

interface AdminRoleServiceInterface
{
    public function getAdminRoleRepository(): AdminRoleRepository;

    /**
     * @throws NotFoundException
     */
    public function findAdminRole(string $id): AdminRole;

    /**
     * @param array<string, mixed> $params
     */
    public function getAdminRoles(array $params): QueryBuilder;
}
