<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\User\Entity\UserAvatar;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Class UserAvatarRepository
 * @package Api\User\Repository
 */
class UserAvatarRepository extends EntityRepository
{
    /**
     * @param UserAvatar $avatar
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAvatar(UserAvatar $avatar)
    {
        $this->getEntityManager()->remove($avatar);
        $this->getEntityManager()->flush();
    }
}
