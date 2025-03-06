<?php

declare(strict_types=1);

namespace Core\User\Enum;

enum UserStatusEnum: string
{
    case Active  = 'active';
    case Pending = 'pending';
    case Deleted = 'deleted';
}
