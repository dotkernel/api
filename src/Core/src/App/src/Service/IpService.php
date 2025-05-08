<?php

declare(strict_types=1);

namespace Core\App\Service;

use function filter_var;
use function getenv;

use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_FLAG_NO_PRIV_RANGE;
use const FILTER_FLAG_NO_RES_RANGE;
use const FILTER_VALIDATE_IP;

class IpService
{
    /**
     * @phpstan-param array{
     *     HTTP_X_FORWARDED_FOR?: string,
     *     HTTP_CLIENT_IP?: string,
     *     REMOTE_ADDR?: string,
     * } $server
     */
    public static function getUserIp(array $server): mixed
    {
        if (! empty($server)) {
            // check if HTTP_X_FORWARDED_FOR is public network IP
            if (isset($server['HTTP_X_FORWARDED_FOR']) && self::isPublicIp($server['HTTP_X_FORWARDED_FOR'])) {
                $realIp = $server['HTTP_X_FORWARDED_FOR'];
            // check if HTTP_CLIENT_IP is public network IP
            } elseif (isset($server['HTTP_CLIENT_IP']) && self::isPublicIp($server['HTTP_CLIENT_IP'])) {
                $realIp = $server['HTTP_CLIENT_IP'];
            } else {
                $realIp = $server['REMOTE_ADDR'];
            }
        } else {
            // check if HTTP_X_FORWARDED_FOR is public network IP
            if (getenv('HTTP_X_FORWARDED_FOR') && self::isPublicIp((string) getenv('HTTP_X_FORWARDED_FOR'))) {
                $realIp = getenv('HTTP_X_FORWARDED_FOR');
            // check if HTTP_CLIENT_IP is public network IP
            } elseif (getenv('HTTP_CLIENT_IP') && self::isPublicIp((string) getenv('HTTP_CLIENT_IP'))) {
                $realIp = getenv('HTTP_CLIENT_IP');
            } else {
                $realIp = getenv('REMOTE_ADDR');
            }
        }

        return $realIp;
    }

    public static function isPublicIp(string $ipAddress): bool
    {
        return filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 |
                FILTER_FLAG_IPV6 |
                FILTER_FLAG_NO_PRIV_RANGE |
                FILTER_FLAG_NO_RES_RANGE
        ) === $ipAddress;
    }
}
