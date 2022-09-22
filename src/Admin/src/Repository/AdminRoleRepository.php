<?php

declare(strict_types=1);

namespace Api\Admin\Repository;

use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Entity\AdminRole;
use Api\App\Helper\PaginationHelper;
use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;

/**
 * Class AdminRoleRepository
 * @package Api\Admin\Repository
 *
 * @Entity(name="Api\Admin\Entity\AdminRole")
 */
class AdminRoleRepository extends EntityRepository
{
    /**
     * @param array $filters
     * @return AdminRoleCollection
     */
    public function getRoles(array $filters = []): AdminRoleCollection
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(['role'])->from(AdminRole::class, 'role');

        // Order results
        $qb->orderBy(($filters['order'] ?? 'role.created'), $filters['dir'] ?? 'desc');

        // Paginate results
        $page = PaginationHelper::getOffsetAndLimit($filters);
        $qb->setFirstResult($page['offset'])->setMaxResults($page['limit']);

        $qb->getQuery()->useQueryCache(true);

        // Return results
        return new AdminRoleCollection($qb, false);
    }
}
