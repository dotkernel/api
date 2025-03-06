<?php

declare(strict_types=1);

namespace Core\User\Repository;

use Core\User\Entity\UserDetail;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: UserDetail::class)]
class UserDetailRepository extends EntityRepository
{
}
