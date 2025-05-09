<?php

declare(strict_types=1);

namespace Core\App\Enum;

use function array_column;

enum YesNoEnum: string
{
    case Yes = 'yes';
    case No  = 'no';

    /**
     * @return non-empty-string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
