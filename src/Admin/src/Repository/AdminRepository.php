<?php

declare(strict_types=1);

namespace Api\Admin\Repository;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Entity\Admin;
use Api\App\Helper\PaginationHelper;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Exception;
use Throwable;

/**
 * Class AdminRepository
 * @package Api\Admin\Repository
 *
 * @Entity(name="Api\Admin\Entity\Admin")
 */
class AdminRepository extends EntityRepository
{
    /**
     * @param Admin $admin
     * @throws Exception
     * @return void
     */
    public function deleteAdmin(Admin $admin): void
    {
        $this->saveAdmin($admin->resetRoles());

        $this->getEntityManager()->remove($admin);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Admin $admin
     * @return void
     */
    public function saveAdmin(Admin $admin): void
    {
        $this->getEntityManager()->persist($admin);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $identity
     * @param string|null $uuid
     * @return Admin|null
     */
    public function exists(string $identity = '', ?string $uuid = ''): ?Admin
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('admin')
            ->from(Admin::class, 'admin')
            ->andWhere('admin.identity = :identity')->setParameter('identity', $identity);
        if (!empty($uuid)) {
            $qb->andWhere('admin.uuid != :uuid')->setParameter('uuid', $uuid, UuidBinaryOrderedTimeType::NAME);
        }

        try {
            return $qb->getQuery()->useQueryCache(true)->getSingleResult();
        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * @param array $filters
     * @return AdminCollection
     */
    public function getAdmins(array $filters = []): AdminCollection
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(['admin'])->from(Admin::class, 'admin');

        // Order results
        $qb->orderBy(($filters['order'] ?? 'admin.created'), $filters['dir'] ?? 'desc');

        // Paginate results
        $page = PaginationHelper::getOffsetAndLimit($filters);
        $qb->setFirstResult($page['offset'])->setMaxResults($page['limit']);

        $qb->getQuery()->useQueryCache(true);

        // Return results
        return new AdminCollection($qb, false);
    }
}
