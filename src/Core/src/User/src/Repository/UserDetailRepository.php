<?php

declare(strict_types=1);

namespace Core\User\Repository;

use Core\App\Repository\AbstractRepository;
use Core\User\Entity\UserDetail;
use Dot\DependencyInjection\Attribute\Entity;

#[Entity(name: UserDetail::class)]
class UserDetailRepository extends AbstractRepository
{
}
