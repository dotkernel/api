<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\User\Entity\UserAvatar;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: UserAvatar::class)]
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
