<?php

declare(strict_types=1);

namespace Core\Admin\Repository;

use Core\Admin\Entity\Admin;
use Core\App\Repository\AbstractRepository;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;

use function array_key_exists;
use function is_string;
use function strlen;

#[Entity(name: Admin::class)]
class AdminRepository extends AbstractRepository
{
    /**
     * @param array<non-empty-string, mixed> $params
     * @param array<non-empty-string, mixed> $filters
     */
    public function getAdmins(array $params = [], array $filters = []): QueryBuilder
    {
        $queryBuilder = $this
            ->getQueryBuilder()
            ->select(['admin'])
            ->from(Admin::class, 'admin')
            ->leftJoin('admin.roles', 'role');

        if (
            array_key_exists('identity', $filters)
            && is_string($filters['identity'])
            && strlen($filters['identity']) > 0
        ) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like('admin.identity', ':identity'))
                ->setParameter('identity', '%' . $filters['identity'] . '%');
        }
        if (
            array_key_exists('status', $filters)
            && is_string($filters['status'])
            && strlen($filters['status']) > 0
        ) {
            $queryBuilder
                ->andWhere('admin.status = :status')
                ->setParameter('status', $filters['status']);
        }
        if (
            array_key_exists('role', $filters)
            && is_string($filters['role'])
            && strlen($filters['role']) > 0
        ) {
            $queryBuilder
                ->andWhere('role.name = :role')
                ->setParameter('role', $filters['role']);
        }

        $queryBuilder
            ->orderBy($params['sort'], $params['dir'])
            ->setFirstResult($params['offset'])
            ->setMaxResults($params['limit'])
            ->groupBy('admin.uuid');
        $queryBuilder->getQuery()->useQueryCache(true);

        return $queryBuilder;
    }
}
