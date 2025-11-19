<?php

declare(strict_types=1);

namespace Core\User\Enum;

use function array_column;

enum UserResetPasswordStatusEnum: string
{
    case Completed = 'completed';
    case Requested = 'requested';

    /**
     * @return non-empty-string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
