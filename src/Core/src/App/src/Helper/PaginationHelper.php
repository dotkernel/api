<?php

declare(strict_types=1);

namespace Core\App\Helper;

use function array_key_exists;

class PaginationHelper
{
    public const LIMIT = 10;

    public static function getOffsetAndLimit(array $filters = []): array
    {
        $page = (int) ($filters['page'] ?? 1);

        if (array_key_exists('all', $filters)) {
            $offset = 0;
            $limit  = 1_000;
        } elseif ($page > 0) {
            $offset = ($page - 1) * self::LIMIT;
            $limit  = self::LIMIT;
        } else {
            $offset = 0;
            $limit  = self::LIMIT;
        }

        return [
            'offset' => $offset,
            'limit'  => $limit,
        ];
    }
}
