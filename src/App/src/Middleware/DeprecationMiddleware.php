<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\App\Attribute\ResourceDeprecation;
use Api\App\Exception\ExpiredException;
use Api\App\Exception\RuntimeException;
use Api\App\Service\HandlerService;
use DateTimeImmutable;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionException;

use function array_filter;
use function array_merge;
use function array_values;
use function implode;
use function is_string;
use function sprintf;

class DeprecationMiddleware implements MiddlewareInterface
{
    public const string RESOURCE_DEPRECATION_ATTRIBUTE = ResourceDeprecation::class;

    /**
     * @param array<non-empty-string, mixed> $config
     */
    #[Inject(
        'config.application.versioning',
    )]
    public function __construct(
        private readonly array $config,
    ) {
    }

    /**
     * @throws ExpiredException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $reflectionHandler = HandlerService::getReflectionHandler($request);
        if (! $reflectionHandler instanceof ReflectionClass) {
            return $handler->handle($request);
        }

        $attributes = $this->getReflectionAttributes($reflectionHandler);
        if (empty($attributes)) {
            return $handler->handle($request);
        }

        $attribute = $this->getAttribute($attributes);
        if (null === $attribute) {
            return $handler->handle($request);
        }

        $documentationUrl = $this->getDocumentationUrl($attribute);

        if ($this->isSunsetReached($attribute['sunset'])) {
            throw ExpiredException::create(
                detail: sprintf('This resource is no longer available (sunset on %s).', $attribute['sunset']),
                type: $documentationUrl ?? '',
            );
        }

        $response = $handler->handle($request);

        if (! empty($attribute['sunset'])) {
            $response = $response->withHeader('sunset', $attribute['sunset']);
        }

        if (null !== $documentationUrl) {
            $response = $response->withHeader('link', $this->formatLink($documentationUrl, $attribute));
        }

        return $response;
    }

    /**
     * @param array<non-empty-string, mixed> $attribute
     * @return non-empty-string|null
     */
    private function getDocumentationUrl(array $attribute): ?string
    {
        /** @var non-empty-string|null $url */
        $url = $attribute['link'] ?? $this->config['documentation_url'] ?? null;

        return is_string($url) ? $url : null;
    }

    private function isSunsetReached(?string $sunset): bool
    {
        if (empty($sunset)) {
            return false;
        }

        return new DateTimeImmutable() >= new DateTimeImmutable($sunset);
    }

    /**
     * @param array<int, mixed> $attributes
     * @return array<non-empty-string, mixed>|null
     */
    private function getAttribute(array $attributes): ?array
    {
        return array_values(
            array_filter(
                $attributes,
                static fn (array $attribute): bool
                => $attribute['deprecationType'] === self::RESOURCE_DEPRECATION_ATTRIBUTE
            )
        )[0] ?? null;
    }

    /**
     * @param ReflectionClass<RequestHandlerInterface> $reflectionObject
     * @return array<int, mixed>
     */
    private function getReflectionAttributes(ReflectionClass $reflectionObject): array
    {
        $attributes = [];

        foreach ($reflectionObject->getAttributes(self::RESOURCE_DEPRECATION_ATTRIBUTE) as $attribute) {
            $attributes[] = array_merge(
                ($attribute->newInstance())->toArray(),
                ['identifier' => $reflectionObject->name]
            );
        }

        return $attributes;
    }

    /**
     * @param non-empty-string $baseLink
     * @param array<non-empty-string, mixed> $attribute
     * @return non-empty-string
     */
    private function formatLink(string $baseLink, array $attribute): string
    {
        $parts = [
            $baseLink,
        ];
        if (! empty($attribute['rel'])) {
            $parts[] = sprintf('rel="%s"', $attribute['rel']);
        }
        if (! empty($attribute['type'])) {
            $parts[] = sprintf('type="%s"', $attribute['type']);
        }

        return implode(';', $parts);
    }
}
