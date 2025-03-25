<?php

declare(strict_types=1);

namespace Core\Admin\Service;

use Core\Admin\Repository\AdminRoleRepository;
use Dot\DependencyInjection\Attribute\Inject;

class AdminRoleService implements AdminRoleServiceInterface
{
    #[Inject(
        AdminRoleRepository::class,
    )]
    public function __construct(
        protected AdminRoleRepository $adminRoleRepository,
    ) {
    }

    public function getAdminRoleRepository(): AdminRoleRepository
    {
        return $this->adminRoleRepository;
    }
}
