<?php

declare(strict_types=1);

namespace Core\Admin\Enum;

use function array_column;

enum AdminRoleEnum: string
{
    case Admin     = 'admin';
    case Superuser = 'superuser';

    /**
     * @return non-empty-string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
