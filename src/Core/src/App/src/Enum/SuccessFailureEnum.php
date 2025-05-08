<?php

declare(strict_types=1);

namespace Core\App\Enum;

use function array_column;

enum SuccessFailureEnum: string
{
    case Success = 'success';
    case Fail    = 'fail';

    /**
     * @return non-empty-string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
