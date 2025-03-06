<?php

declare(strict_types=1);

namespace Core\Admin\Repository;

use Core\Admin\Entity\AdminRole;
use Core\App\Helper\PaginationHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: AdminRole::class)]
class AdminRoleRepository extends EntityRepository
{
    public function getAdminRoles(array $filters = []): QueryBuilder
    {
        $page = PaginationHelper::getOffsetAndLimit($filters);

        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['role'])
            ->from(AdminRole::class, 'role')
            ->orderBy($filters['order'] ?? 'role.created', $filters['dir'] ?? 'desc')
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit']);
    }
}
