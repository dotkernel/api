<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\User\Entity\UserAvatar;
use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;

/**
 * @Entity(name="Api\User\Entity\UserAvatar")
 * @extends EntityRepository<object>
 */
class UserAvatarRepository extends EntityRepository
{
    public function deleteAvatar(UserAvatar $avatar): void
    {
        $this->getEntityManager()->remove($avatar);
        $this->getEntityManager()->flush();
    }

    public function saveAvatar(UserAvatar $avatar): UserAvatar
    {
        $this->getEntityManager()->persist($avatar);
        $this->getEntityManager()->flush();

        return $avatar;
    }
}
