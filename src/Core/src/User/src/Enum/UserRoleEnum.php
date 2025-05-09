<?php

declare(strict_types=1);

namespace Core\User\Enum;

use function array_filter;

enum UserRoleEnum: string
{
    case Guest = 'guest';
    case User  = 'user';

    /**
     * @return array<int, self>
     */
    public static function validCases(): array
    {
        return array_filter(self::cases(), fn (self $value) => $value !== self::Guest);
    }
}
