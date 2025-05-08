<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\App\Attribute\MethodDeprecation;
use Api\App\Attribute\ResourceDeprecation;
use Api\App\Exception\ConflictException;
use Api\App\Exception\RuntimeException;
use Api\App\Service\HandlerService;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

use function array_column;
use function array_filter;
use function array_intersect;
use function array_merge;
use function array_values;
use function count;
use function implode;
use function is_string;
use function sprintf;

class DeprecationMiddleware implements MiddlewareInterface
{
    public const RESOURCE_DEPRECATION_ATTRIBUTE = ResourceDeprecation::class;
    public const METHOD_DEPRECATION_ATTRIBUTE   = MethodDeprecation::class;

    public const DEPRECATION_ATTRIBUTES = [
        self::RESOURCE_DEPRECATION_ATTRIBUTE,
        self::METHOD_DEPRECATION_ATTRIBUTE,
    ];

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
     * @throws ConflictException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);

        $reflectionHandler = HandlerService::getReflectionHandler($request);
        if (! $reflectionHandler instanceof ReflectionClass) {
            return $response;
        }

        $attributes = $this->getReflectionAttributes($reflectionHandler);
        if (empty($attributes)) {
            return $response;
        }

        $this->validateAttributes($attributes);
        $attribute = $this->getAttribute($attributes);
        if (null === $attribute) {
            return $response;
        }

        if (! empty($attribute['sunset'])) {
            $response = $response->withHeader('sunset', $attribute['sunset']);
        }

        /** @var non-empty-string $baseUrl */
        $baseUrl = $attribute['link'] ?? $this->config['documentation_url'] ?? null;
        if (is_string($baseUrl)) {
            $response = $response->withHeader('link', $this->formatLink($baseUrl, $attribute));
        }

        return $response;
    }

    /**
     * @param array<int, mixed> $attributes
     * @return array<non-empty-string, mixed>|null
     */
    private function getAttribute(array $attributes): ?array
    {
        $attribute = array_values(
            array_filter(
                $attributes,
                fn (array $attribute): bool => $attribute['deprecationType'] === self::RESOURCE_DEPRECATION_ATTRIBUTE
            )
        )[0] ?? null;

        if (null === $attribute) {
            $attribute = array_values(
                array_filter(
                    $attributes,
                    fn (array $attribute): bool => $attribute['deprecationType'] === self::METHOD_DEPRECATION_ATTRIBUTE
                )
            )[0] ?? null;
        }

        return $attribute;
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

        foreach ($reflectionObject->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
            foreach ($refMethod->getAttributes(self::METHOD_DEPRECATION_ATTRIBUTE) as $attribute) {
                $attributes[] = array_merge(($attribute->newInstance())->toArray(), ['identifier' => $refMethod->name]);
            }
        }

        return $attributes;
    }

    /**
     * @param array<int, mixed> $attributes
     * @throws ConflictException
     */
    private function validateAttributes(array $attributes): void
    {
        $intersect = array_intersect(self::DEPRECATION_ATTRIBUTES, array_column($attributes, 'deprecationType'));
        if (count($intersect) === count(self::DEPRECATION_ATTRIBUTES)) {
            throw ConflictException::create(
                sprintf(
                    Message::RESTRICTION_DEPRECATION,
                    self::RESOURCE_DEPRECATION_ATTRIBUTE,
                    self::METHOD_DEPRECATION_ATTRIBUTE
                )
            );
        }
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
