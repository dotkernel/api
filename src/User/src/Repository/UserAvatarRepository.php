<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\User\Entity\UserAvatar;
use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;

/**
 * Class UserAvatarRepository
 * @package Api\User\Repository
 *
 * @Entity(name="Api\User\Entity\UserAvatar")
 */
class UserAvatarRepository extends EntityRepository
{
    /**
     * @param UserAvatar $avatar
     * @return void
     */
    public function deleteAvatar(UserAvatar $avatar): void
    {
        $this->getEntityManager()->remove($avatar);
        $this->getEntityManager()->flush();
    }

    /**
     * @param UserAvatar $avatar
     * @return UserAvatar
     */
    public function saveAvatar(UserAvatar $avatar): UserAvatar
    {
        $this->getEntityManager()->persist($avatar);
        $this->getEntityManager()->flush();

        return $avatar;
    }
}
