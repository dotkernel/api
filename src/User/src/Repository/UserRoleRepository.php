<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\App\Helper\PaginationHelper;
use Api\User\Collection\UserRoleCollection;
use Api\User\Entity\UserRole;
use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;

/**
 * @Entity(name="Api\User\Entity\UserRole")
 * @extends EntityRepository<object>
 */
class UserRoleRepository extends EntityRepository
{
    public function getRoles(array $params = []): UserRoleCollection
    {
        $page = PaginationHelper::getOffsetAndLimit($params);

        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['role'])
            ->from(UserRole::class, 'role')
            ->orderBy($params['order'] ?? 'role.created', $params['dir'] ?? 'desc')
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit'])
            ->getQuery()
            ->setCacheable(true);

        return new UserRoleCollection($query, false);
    }
}
