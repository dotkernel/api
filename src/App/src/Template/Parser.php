<?php

declare(strict_types=1);

namespace Api\App\Template;

use Dot\DependencyInjection\Attribute\Inject;
use Mezzio\Helper\UrlHelperInterface;

use function array_key_exists;
use function assert;
use function sprintf;

class Parser implements ParserInterface
{
    /** @var array<non-empty-string, mixed> $globals */
    protected array $globals = [];

    /**
     * @param array<non-empty-string, mixed> $config
     */
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

        $absoluteUrl = sprintf('%s%s', $baseUrl, $path);
        assert($absoluteUrl !== '');

        return $absoluteUrl;
    }

    public function url(
        ?string $routeName = null,
        array $routeParams = [],
        array $queryParams = [],
        ?string $fragmentIdentifier = null,
        array $options = []
    ): string {
        $url = $this->urlHelper->generate($routeName, $routeParams, $queryParams, $fragmentIdentifier, $options);
        assert($url !== '');

        return $url;
    }

    /**
     * @return array<non-empty-string, mixed>
     */
    public function getGlobals(): array
    {
        return $this->globals;
    }

    /**
     * @param array<non-empty-string, mixed> $config
     * @return array<non-empty-string, mixed>
     */
    public function prepareGlobals(array $config = []): array
    {
        $globals = $config[RendererInterface::class]['globals'] ?? [];

        if (array_key_exists('application', $config)) {
            $globals['application'] = $config['application'] ?? [];
        }

        return $globals;
    }
}
