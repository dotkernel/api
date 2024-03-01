<?php

declare(strict_types=1);

namespace Api\Admin\Repository;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Entity\Admin;
use Api\App\Helper\PaginationHelper;
use Api\App\Message;
use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;
use Exception;

/**
 * @Entity(name="Api\Admin\Entity\Admin")
 * @extends EntityRepository<object>
 */
class AdminRepository extends EntityRepository
{
    /**
     * @throws Exception
     */
    public function deleteAdmin(Admin $admin): void
    {
        $this->getEntityManager()->remove($admin);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws Exception
     */
    public function saveAdmin(Admin $admin): Admin
    {
        if (! $admin->hasRoles()) {
            throw new Exception(Message::RESTRICTION_ROLES);
        }

        $this->getEntityManager()->persist($admin);
        $this->getEntityManager()->flush();

        return $admin;
    }

    public function getAdmins(array $filters = []): AdminCollection
    {
        $page = PaginationHelper::getOffsetAndLimit($filters);

        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['admin'])
            ->from(Admin::class, 'admin')
            ->orderBy($filters['order'] ?? 'admin.created', $filters['dir'] ?? 'desc')
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit'])
            ->getQuery()
            ->setCacheable(true);

        return new AdminCollection($query, false);
    }
}
