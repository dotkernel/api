<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Core\Admin\Entity\AdminRole;
use Core\Admin\Repository\AdminRoleRepository;
use Core\App\Exception\NotFoundException;
use Core\App\Helper\Paginator;
use Core\App\Message;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Inject;

use function in_array;

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
    public function findAdminRole(string $id): AdminRole
    {
        $adminRole = $this->adminRoleRepository->find($id);
        if (! $adminRole instanceof AdminRole) {
            throw new NotFoundException(Message::ROLE_NOT_FOUND);
        }

        return $adminRole;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getAdminRoles(array $params): QueryBuilder
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

        return $this->adminRoleRepository->getAdminRoles($params, $filters);
    }
}
