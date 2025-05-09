<?php

declare(strict_types=1);

namespace Core\User\Enum;

use function array_column;
use function array_filter;
use function array_reduce;

enum UserStatusEnum: string
{
    case Active  = 'active';
    case Pending = 'pending';
    case Deleted = 'deleted';

    /**
     * @return non-empty-string[]
     */
    public static function values(): array
    {
        return array_column(self::validCases(), 'value');
    }

    /**
     * @return self[]
     */
    public static function validCases(): array
    {
        return array_filter(self::cases(), fn (self $enum) => $enum !== self::Deleted);
    }

    /**
     * @return array<string, non-empty-string>
     */
    public static function toArray(): array
    {
        return array_reduce(self::validCases(), function (array $collector, self $enum): array {
            $collector[$enum->value] = $enum->name;

            return $collector;
        }, []);
    }
}
