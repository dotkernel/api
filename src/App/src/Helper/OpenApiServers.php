<?php

declare(strict_types=1);

namespace Api\App\Helper;

use InvalidArgumentException;

use function array_key_exists;
use function array_values;
use function is_array;
use function is_string;
use function sprintf;

/**
 * The `servers` list of the generated OpenAPI document, built by bin/generate-openapi.php.
 *
 * The project's own URL always comes first, followed by the additional servers from `openapi.servers` in
 * their configured order. Each URL is listed once: the first occurrence wins, so the project's own entry keeps
 * its description even when `openapi.servers` names the same URL again.
 */
final class OpenApiServers
{
    /**
     * @return list<array{url: non-empty-string, description: string|null}>
     * @throws InvalidArgumentException When `openapi.servers` or one of its entries is malformed.
     */
    public static function fromConfig(string $baseUrl, ?string $baseDescription, mixed $additionalServers): array
    {
        if ($baseUrl === '') {
            throw new InvalidArgumentException('The project URL must be a non-empty string.');
        }

        if (! is_array($additionalServers)) {
            throw new InvalidArgumentException('The `openapi.servers` config key must be an array.');
        }

        $servers = [$baseUrl => ['url' => $baseUrl, 'description' => $baseDescription]];

        foreach ($additionalServers as $index => $server) {
            $url = is_array($server) ? ($server['url'] ?? null) : null;
            if (! is_string($url) || $url === '') {
                throw new InvalidArgumentException(sprintf(
                    'The `openapi.servers` entry "%s" needs a non-empty string `url`.',
                    $index,
                ));
            }

            $description = $server['description'] ?? null;
            if ($description !== null && ! is_string($description)) {
                throw new InvalidArgumentException(sprintf(
                    'The `openapi.servers` entry "%s" has a `description` that is not a string.',
                    $index,
                ));
            }

            if (array_key_exists($url, $servers)) {
                continue;
            }

            $servers[$url] = ['url' => $url, 'description' => $description];
        }

        return array_values($servers);
    }
}
