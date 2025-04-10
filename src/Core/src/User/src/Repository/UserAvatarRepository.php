<?php

declare(strict_types=1);

namespace Core\User\Repository;

use Core\App\Repository\AbstractRepository;
use Core\User\Entity\UserAvatar;
use Dot\DependencyInjection\Attribute\Entity;

#[Entity(name: UserAvatar::class)]
class UserAvatarRepository extends AbstractRepository
{
}
