<?php

declare(strict_types=1);

namespace Api\App\Helper;

class PaginationHelper
{
    public const LIMIT = 10;

    public static function getOffsetAndLimit(array $filters = []): array
    {
        $page  = (int) ($filters['page'] ?? 1);
        $limit = (int) ($filters['limit'] ?? self::LIMIT);

        $offset = 0;
        if ($page > 0) {
            $offset = ($page - 1) * $limit;
        }

        return [
            'offset' => (int) $offset,
            'limit'  => $limit,
        ];
    }
}
