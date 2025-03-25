<?php

declare(strict_types=1);

namespace Core\User\Service;

use Core\User\Repository\UserRoleRepository;
use Dot\DependencyInjection\Attribute\Inject;

class UserRoleService implements UserRoleServiceInterface
{
    #[Inject(
        UserRoleRepository::class,
    )]
    public function __construct(
        protected UserRoleRepository $roleRepository,
    ) {
    }

    public function getRoleRepository(): UserRoleRepository
    {
        return $this->roleRepository;
    }
}
