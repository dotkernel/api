<?php

declare(strict_types=1);

namespace Core\Admin\Repository;

use Core\Admin\Entity\AdminLogin;
use Core\App\Repository\AbstractRepository;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;

use function array_column;
use function array_key_exists;
use function is_string;
use function strlen;

#[Entity(AdminLogin::class)]
class AdminLoginRepository extends AbstractRepository
{
    /**
     * @return non-empty-string[]
     */
    public function getAdminLoginIdentities(): array
    {
        $results = $this->getQueryBuilder()
            ->select('DISTINCT adminLogin.identity')
            ->from(AdminLogin::class, 'adminLogin')
            ->orderBy('adminLogin.identity', 'ASC')
            ->getQuery()->getResult();

        return array_column($results, 'identity');
    }

    /**
     * @param array<non-empty-string, mixed> $params
     * @param array<non-empty-string, mixed> $filters
     */
    public function getAdminLogins(array $params = [], array $filters = []): QueryBuilder
    {
        $queryBuilder = $this
            ->getQueryBuilder()
            ->select(['login'])
            ->from(AdminLogin::class, 'login');

        if (
            array_key_exists('identity', $filters)
            && is_string($filters['identity'])
            && strlen($filters['identity']) > 0
        ) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like('login.identity', ':search'))
                ->setParameter('search', '%' . $filters['identity'] . '%');
        }
        if (
            array_key_exists('status', $filters)
            && is_string($filters['status'])
            && strlen($filters['status']) > 0
        ) {
            $queryBuilder
                ->andWhere('login.loginStatus = :status')
                ->setParameter('status', $filters['status']);
        }

        $queryBuilder
            ->orderBy($params['sort'], $params['dir'])
            ->setFirstResult($params['offset'])
            ->setMaxResults($params['limit']);
        $queryBuilder->getQuery()->useQueryCache(true);

        return $queryBuilder;
    }
}
