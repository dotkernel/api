<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\NotFoundException;
use Core\App\Helper\Paginator;
use Core\App\Message;
use Core\User\Entity\UserRole;
use Core\User\Repository\UserRoleRepository;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Inject;

use function in_array;

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
    public function findUserRole(string $id): UserRole
    {
        $userRole = $this->userRoleRepository->find($id);
        if (! $userRole instanceof UserRole) {
            throw NotFoundException::create(Message::ROLE_NOT_FOUND);
        }

        return $userRole;
    }

    /**
     * @param array<non-empty-string, mixed> $params
     */
    public function getUserRoles(array $params): QueryBuilder
    {
        $filters = $params['filters'] ?? [];
        $params  = Paginator::getParams($params, 'role.created');

        $sortableColumns = [
            'role.name',
            'role.created',
            'role.updated',
        ];
        if (! in_array($params['sort'], $sortableColumns, true)) {
            $params['sort'] = 'role.created';
        }

        return $this->userRoleRepository->getUserRoles($params, $filters);
    }
}
