<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\User\Entity\UserDetail;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: UserDetail::class)]
class UserDetailRepository extends EntityRepository
{
}
