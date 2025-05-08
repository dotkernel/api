<?php

declare(strict_types=1);

namespace Core\App\Helper;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

use function array_key_exists;
use function ceil;
use function in_array;
use function is_numeric;
use function is_string;
use function max;
use function min;
use function range;
use function strlen;

/**
 * @template T
 */
class Paginator
{
    /**
     * @param array<non-empty-string, mixed> $params
     * @param non-empty-string $sort
     * @param non-empty-string $dir
     * @return array{
     *     offset: int,
     *     limit: int,
     *     page: int,
     *     sort: non-empty-string,
     *     dir: non-empty-string
     * }
     */
    public static function getParams(array $params, string $sort, string $dir = 'desc'): array
    {
        $offset = 0;
        $limit  = 10;
        $page   = 1;

        if (array_key_exists('sort', $params) && is_string($params['sort']) && strlen($params['sort']) > 0) {
            $sort = $params['sort'];
        }

        if (array_key_exists('dir', $params) && in_array($params['dir'], ['asc', 'desc'], true)) {
            $dir = $params['dir'];
        }

        if (array_key_exists('all', $params)) {
            return [
                'offset' => $offset,
                'limit'  => 1_000,
                'page'   => $page,
                'sort'   => $sort,
                'dir'    => $dir,
            ];
        }

        if (array_key_exists('limit', $params) && is_numeric($params['limit']) && $params['limit'] > 0) {
            $limit = (int) $params['limit'];
        }

        if (array_key_exists('offset', $params) && is_numeric($params['offset']) && $params['offset'] > 0) {
            $offset = (int) $params['offset'];
            $page   = ($offset + $limit) / $limit;
        }

        if (array_key_exists('page', $params) && is_numeric($params['page']) && $params['page'] > 0) {
            $page   = (int) $params['page'];
            $offset = ($page - 1) * $limit;
        }

        return [
            'offset' => $offset,
            'limit'  => $limit,
            'page'   => $page,
            'sort'   => $sort,
            'dir'    => $dir,
        ];
    }

    /**
     * @param DoctrinePaginator<T> $paginator
     * @param array<non-empty-string, mixed> $params
     * @param array<non-empty-string, mixed> $filters
     * @return array<non-empty-string, mixed>
     */
    public static function wrapper(DoctrinePaginator $paginator, array $params = [], array $filters = []): array
    {
        $params['count']   = $paginator->count();
        $params['items']   = $paginator->getQuery()->getResult();
        $params['filters'] = $filters;

        $params['currentPage']     = (int) ceil($params['offset'] / $params['limit']) + 1;
        $params['firstPage']       = 1;
        $params['previousPage']    = max($params['currentPage'] - 1, 1);
        $params['lastPage']        = $params['count'] > 0
            ? (int) ceil($params['count'] / $params['limit'])
            : $params['firstPage'];
        $params['isOutOfBounds']   = $params['currentPage'] > $params['lastPage'];
        $params['nextPage']        = min($params['currentPage'] + 1, $params['lastPage']);
        $params['isFirstPage']     = $params['page'] === $params['firstPage'];
        $params['isLastPage']      = $params['currentPage'] === $params['lastPage'];
        $params['hasPreviousPage'] = $params['currentPage'] > $params['firstPage'];
        $params['hasNextPage']     = $params['currentPage'] < $params['lastPage'];
        if ($params['isOutOfBounds']) {
            $params['previousPage'] = max($params['lastPage'], 1);
            $params['hasNextPage']  = false;
            $params['isLastPage']   = true;
        }

        $params['firstOffset']    = 0;
        $params['previousOffset'] = max(0, $params['offset'] - $params['limit']);
        $params['lastOffset']     = ($params['lastPage'] - 1) * $params['limit'];
        $params['nextOffset']     = min($params['offset'] + $params['limit'], $params['lastOffset']);
        if ($params['isOutOfBounds']) {
            $params['previousOffset'] = $params['lastOffset'];
        }

        $params['range'] = 5;
        if ($params['isOutOfBounds']) {
            $params['pages'] = range(
                max(1, $params['lastPage'] - $params['range']),
                min($params['lastPage'], $params['currentPage'] + $params['range'])
            );
        } else {
            $params['pages'] = range(
                max(1, $params['currentPage'] - $params['range']),
                min($params['lastPage'], $params['currentPage'] + $params['range'])
            );
        }

        $params['queryParams'] = [
            'filters' => $params['filters'],
            'offset'  => $params['offset'],
            'limit'   => $params['limit'],
            'sort'    => $params['sort'],
            'dir'     => $params['dir'],
        ];

        return $params;
    }
}
