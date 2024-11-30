<?php

declare(strict_types=1);

namespace Core\User\Repository;

use Core\User\Entity\UserResetPassword;
use Doctrine\ORM\EntityRepository;
use Dot\DependencyInjection\Attribute\Entity;

/**
 * @extends EntityRepository<object>
 */
#[Entity(name: UserResetPassword::class)]
class UserResetPasswordRepository extends EntityRepository
{
}
