<?php

declare(strict_types=1);

namespace Core\User\Repository;

use Core\App\Helper\PaginationHelper;
use Core\User\Entity\UserRole;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: UserRole::class)]
class UserRoleRepository extends EntityRepository
{
    public function getRoles(array $params = []): QueryBuilder
    {
        $page = PaginationHelper::getOffsetAndLimit($params);

        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['role'])
            ->from(UserRole::class, 'role')
            ->orderBy($params['order'] ?? 'role.created', $params['dir'] ?? 'desc')
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit']);
    }
}
