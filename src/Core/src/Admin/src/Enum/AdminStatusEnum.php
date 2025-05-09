<?php

declare(strict_types=1);

namespace Core\Admin\Enum;

use function array_column;
use function array_reduce;

enum AdminStatusEnum: string
{
    case Active   = 'active';
    case Inactive = 'inactive';

    /**
     * @return non-empty-string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, non-empty-string>
     */
    public static function toArray(): array
    {
        return array_reduce(self::cases(), function (array $collector, self $enum): array {
            $collector[$enum->value] = $enum->name;

            return $collector;
        }, []);
    }
}
