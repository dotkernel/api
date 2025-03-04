<?php

declare(strict_types=1);

namespace Api\User\Enum;

enum UserStatusEnum: string
{
    case Active  = 'active';
    case Pending = 'pending';
    case Deleted = 'deleted';
}
