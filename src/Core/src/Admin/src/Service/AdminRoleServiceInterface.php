<?php

declare(strict_types=1);

namespace Core\Admin\Service;

use Core\Admin\Repository\AdminRoleRepository;

interface AdminRoleServiceInterface
{
    public function getAdminRoleRepository(): AdminRoleRepository;
}
