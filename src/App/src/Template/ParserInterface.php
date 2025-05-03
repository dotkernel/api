<?php

declare(strict_types=1);

namespace Api\App\Template;

interface ParserInterface
{
    public function absoluteUrl(string $path, ?string $baseUrl = null): string;

    public function url(
        ?string $routeName = null,
        array $routeParams = [],
        array $queryParams = [],
        ?string $fragmentIdentifier = null,
        array $options = []
    ): string;
}
