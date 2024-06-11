<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Api\User\Entity\UserResetPassword;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: UserResetPassword::class)]
class UserResetPasswordRepository extends EntityRepository
{
}
