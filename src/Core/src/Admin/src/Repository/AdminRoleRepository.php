<?php

declare(strict_types=1);

namespace Core\Admin\Repository;

use Core\Admin\Entity\AdminRole;
use Core\App\Repository\AbstractRepository;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;

use function array_key_exists;
use function is_string;
use function strlen;

#[Entity(name: AdminRole::class)]
class AdminRoleRepository extends AbstractRepository
{
    /**
     * @param array<non-empty-string, mixed> $params
     * @param array<non-empty-string, mixed> $filters
     */
    public function getAdminRoles(array $params = [], array $filters = []): QueryBuilder
    {
        $queryBuilder = $this
            ->getQueryBuilder()
            ->select(['role'])
            ->from(AdminRole::class, 'role');

        if (
            array_key_exists('name', $filters)
            && is_string($filters['name'])
            && strlen($filters['name']) > 0
        ) {
            $queryBuilder
                ->andWhere('role.name = :name')
                ->setParameter('name', $filters['name']);
        }

        $queryBuilder
            ->orderBy($params['sort'], $params['dir'])
            ->setFirstResult($params['offset'])
            ->setMaxResults($params['limit']);
        $queryBuilder->getQuery()->useQueryCache(true);

        return $queryBuilder;
    }
}
