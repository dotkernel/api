<?php

declare(strict_types=1);

namespace Core\User\Service;

use Core\App\Exception\NotFoundException;
use Core\App\Message;
use Core\User\Entity\UserRole;
use Core\User\Repository\UserRoleRepository;
use Dot\DependencyInjection\Attribute\Inject;

class UserRoleService implements UserRoleServiceInterface
{
    #[Inject(
        UserRoleRepository::class,
    )]
    public function __construct(
        protected UserRoleRepository $userRoleRepository,
    ) {
    }

    public function getUserRoleRepository(): UserRoleRepository
    {
        return $this->userRoleRepository;
    }

    /**
     * @throws NotFoundException
     */
    public function find(string $id): UserRole
    {
        $userRole = $this->userRoleRepository->find($id);
        if (! $userRole instanceof UserRole) {
            throw new NotFoundException(Message::ROLE_NOT_FOUND);
        }

        return $userRole;
    }
}
