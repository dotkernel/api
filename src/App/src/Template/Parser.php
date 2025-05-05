<?php

declare(strict_types=1);

namespace Api\App\Template;

use Dot\DependencyInjection\Attribute\Inject;
use Mezzio\Helper\UrlHelperInterface;

use function array_key_exists;
use function sprintf;

class Parser implements ParserInterface
{
    protected array $globals = [];

    #[Inject(
        UrlHelperInterface::class,
        'config',
    )]
    public function __construct(
        protected UrlHelperInterface $urlHelper,
        array $config = [],
    ) {
        $this->globals = $this->prepareGlobals($config);
    }

    public function __invoke(string $path, array $params = []): void
    {
        foreach ($params as $key => $value) {
            ${$key} = $value;
        }
        foreach ($this->globals as $key => $value) {
            ${$key} = $value;
        }

        include $path;
    }

    public function absoluteUrl(string $path, ?string $baseUrl = null): string
    {
        if ($baseUrl === null) {
            $baseUrl = $this->globals['application']['url'] ?? '';
        }

        return sprintf('%s%s', $baseUrl, $path);
    }

    public function url(
        ?string $routeName = null,
        array $routeParams = [],
        array $queryParams = [],
        ?string $fragmentIdentifier = null,
        array $options = []
    ): string {
        return $this->urlHelper->generate($routeName, $routeParams, $queryParams, $fragmentIdentifier, $options);
    }

    public function getGlobals(): array
    {
        return $this->globals;
    }

    public function prepareGlobals(array $config = []): array
    {
        $globals = $config[RendererInterface::class]['globals'] ?? [];

        if (array_key_exists('application', $config)) {
            $globals['application'] = $config['application'] ?? [];
        }

        return $globals;
    }
}
