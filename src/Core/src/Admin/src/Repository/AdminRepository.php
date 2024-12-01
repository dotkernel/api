<?php

declare(strict_types=1);

namespace Core\Admin\Repository;

use Core\Admin\Entity\Admin;
use Core\App\Helper\PaginationHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Dot\DependencyInjection\Attribute\Entity;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: Admin::class)]
class AdminRepository extends EntityRepository
{
    public function deleteAdmin(Admin $admin): void
    {
        $this->getEntityManager()->remove($admin);
        $this->getEntityManager()->flush();
    }

    public function saveAdmin(Admin $admin): Admin
    {
        $this->getEntityManager()->persist($admin);
        $this->getEntityManager()->flush();

        return $admin;
    }

    public function getAdmins(array $filters = []): QueryBuilder
    {
        $page = PaginationHelper::getOffsetAndLimit($filters);

        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['admin'])
            ->from(Admin::class, 'admin')
            ->orderBy($filters['order'] ?? 'admin.created', $filters['dir'] ?? 'desc')
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit']);
    }
}
