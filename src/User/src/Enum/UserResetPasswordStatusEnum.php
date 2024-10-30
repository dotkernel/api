<?php

declare(strict_types=1);

namespace Api\User\Enum;

enum UserResetPasswordStatusEnum: string
{
    case Completed = 'completed';
    case Requested = 'requested';
}
