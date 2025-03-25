<?php

declare(strict_types=1);

namespace Core\User\Service;

use Core\User\Repository\UserRoleRepository;

interface UserRoleServiceInterface
{
    public function getRoleRepository(): UserRoleRepository;
}
