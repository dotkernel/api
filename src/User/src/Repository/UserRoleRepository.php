<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\App\Helper\PaginationHelper;
use Api\User\Collection\UserRoleCollection;
use Api\User\Entity\UserRole;
use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;

/**
 * Class UserRoleRepository
 * @package Api\User\Repository
 *
 * @Entity(name="Api\User\Entity\UserRole")
 */
class UserRoleRepository extends EntityRepository
{
    /**
     * @param array $params
     * @return UserRoleCollection
     */
    public function getRoles(array $params = []): UserRoleCollection
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(['role'])->from(UserRole::class, 'role');

        // Order results
        $qb->orderBy(($params['order'] ?? 'role.created'), $params['dir'] ?? 'desc');

        // Paginate results
        $page = PaginationHelper::getOffsetAndLimit($params);
        $qb->setFirstResult($page['offset'])->setMaxResults($page['limit']);

        $qb->getQuery()->useQueryCache(true);

        // Return results
        return new UserRoleCollection($qb, false);
    }
}
