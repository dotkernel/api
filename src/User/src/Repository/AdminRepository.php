<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\User\Entity\Admin;
use Doctrine\ORM\EntityRepository;

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
}
