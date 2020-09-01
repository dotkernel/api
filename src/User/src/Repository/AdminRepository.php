<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\User\Entity\Admin;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

/**
 * Class AdminRepository
 * @package Api\User\Repository
 */
class AdminRepository extends EntityRepository
{
    /**
     * @param Admin $admin
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveAdmin(Admin $admin)
    {
        $this->getEntityManager()->persist($admin);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Admin $admin
     * @return |null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteAdmin(Admin $admin)
    {
        $this->getEntityManager()->remove($admin);
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $identity
     * @param string|null $uuid
     * @return int|mixed|string|null
     */
    public function exists(string $identity = '', ?string $uuid = '')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('admin')
            ->from(Admin::class, 'admin')
            ->andWhere('admin.identity = :identity')->setParameter('identity', $identity);
        if (!empty($uuid)) {
            $qb->andWhere('admin.uuid != :uuid')->setParameter('uuid', $uuid, UuidBinaryOrderedTimeType::NAME);
        }

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (\Exception $exception) {
            return null;
        }
    }
}
