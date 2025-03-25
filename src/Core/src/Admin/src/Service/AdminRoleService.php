<?php

declare(strict_types=1);

namespace Core\Admin\Service;

use Core\Admin\Entity\AdminRole;
use Core\Admin\Repository\AdminRoleRepository;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
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

    /**
     * @throws NotFoundException
     */
    public function find(string $id): AdminRole
    {
        $adminRole = $this->adminRoleRepository->find($id);
        if (! $adminRole instanceof AdminRole) {
            throw new NotFoundException(Message::ROLE_NOT_FOUND);
        }

        return $adminRole;
    }
}
