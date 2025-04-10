<?php

declare(strict_types=1);

namespace Core\User\Repository;

use Core\App\Repository\AbstractRepository;
use Core\User\Entity\UserResetPassword;
use Dot\DependencyInjection\Attribute\Entity;

#[Entity(name: UserResetPassword::class)]
class UserResetPasswordRepository extends AbstractRepository
{
}
