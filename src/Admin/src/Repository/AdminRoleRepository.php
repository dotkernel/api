<?php

declare(strict_types=1);

namespace Api\Admin\Repository;

use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Entity\AdminRole;
use Api\App\Helper\PaginationHelper;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: AdminRole::class)]
class AdminRoleRepository extends EntityRepository
{
    public function getAdminRoles(array $filters = []): AdminRoleCollection
    {
        $page = PaginationHelper::getOffsetAndLimit($filters);

        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['role'])
            ->from(AdminRole::class, 'role')
            ->orderBy($filters['order'] ?? 'role.created', $filters['dir'] ?? 'desc')
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit'])
            ->getQuery()
            ->useQueryCache(true);

        return new AdminRoleCollection($query, false);
    }
}
