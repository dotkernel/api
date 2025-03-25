<?php

declare(strict_types=1);

namespace Core\Admin\Service;

use Core\Admin\Entity\AdminRole;
use Core\Admin\Repository\AdminRoleRepository;
use Core\App\Exception\NotFoundException;

interface AdminRoleServiceInterface
{
    public function getAdminRoleRepository(): AdminRoleRepository;

    /**
     * @throws NotFoundException
     */
    public function find(string $id): AdminRole;
}
