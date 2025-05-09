<?php

declare(strict_types=1);

namespace Api\App\Template;

interface ParserInterface
{
    /**
     * @param non-empty-string $path
     * @param array<non-empty-string, mixed> $params
     */
    public function __invoke(string $path, array $params = []): void;

    /**
     * @param non-empty-string $path
     * @param non-empty-string|null $baseUrl
     */
    public function absoluteUrl(string $path, ?string $baseUrl = null): string;

    /**
     * @param non-empty-string|null $routeName
     * @param array<non-empty-string, mixed> $routeParams
     * @param array<non-empty-string, mixed> $queryParams
     * @param non-empty-string|null $fragmentIdentifier
     * @param array<non-empty-string, mixed> $options
     */
    public function url(
        ?string $routeName = null,
        array $routeParams = [],
        array $queryParams = [],
        ?string $fragmentIdentifier = null,
        array $options = []
    ): string;
}
