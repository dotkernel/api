<?php

declare(strict_types=1);

namespace Api\Admin\Repository;

use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Entity\AdminRole;
use Api\App\Helper\PaginationHelper;
use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;

/**
 * @Entity(name="Api\Admin\Entity\AdminRole")
 * @extends EntityRepository<object>
 */
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
            ->setCacheable(true);

        return new AdminRoleCollection($query, false);
    }
}
