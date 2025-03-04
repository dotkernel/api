<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\App\Attribute\MethodDeprecation;
use Api\App\Attribute\ResourceDeprecation;
use Api\App\Exception\DeprecationConflictException;
use Api\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Laminas\Stratigility\MiddlewarePipe;
use Mezzio\Middleware\LazyLoadingMiddleware;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

use function array_column;
use function array_filter;
use function array_intersect;
use function array_merge;
use function array_values;
use function count;
use function implode;
use function is_string;
use function sprintf;
use function strtoupper;

class DeprecationMiddleware implements MiddlewareInterface
{
    public const RESOURCE_DEPRECATION_ATTRIBUTE = ResourceDeprecation::class;
    public const METHOD_DEPRECATION_ATTRIBUTE   = MethodDeprecation::class;

    public const DEPRECATION_ATTRIBUTES = [
        self::RESOURCE_DEPRECATION_ATTRIBUTE,
        self::METHOD_DEPRECATION_ATTRIBUTE,
    ];

    #[Inject(
        "config.application.versioning",
    )]
    public function __construct(
        protected readonly array $config,
    ) {
    }

    /**
     * @throws ReflectionException
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response    = $handler->handle($request);
        $routeResult = $request->getAttribute(RouteResult::class);
        if (! $routeResult instanceof RouteResult || $routeResult->isFailure()) {
            return $response;
        }

        $matchedRoute = $routeResult->getMatchedRoute();
        if (! $matchedRoute) {
            return $response;
        }

        $reflectionHandler = $this->getHandler($matchedRoute->getMiddleware());
        if (empty($reflectionHandler)) {
            return $response;
        }

        $attributes = $this->getReflectionAttributes($reflectionHandler);
        if (empty($attributes)) {
            return $response;
        }

        $this->validateAttributes($attributes);
        $attribute = $this->getAttribute($attributes, $request->getMethod());
        if (null === $attribute) {
            return $response;
        }

        if (! empty($attribute['sunset'])) {
            $response = $response->withHeader('sunset', $attribute['sunset']);
        }

        $baseUrl = $attribute['link'] ?? $this->config['documentation_url'] ?? null;
        if (is_string($baseUrl)) {
            $response = $response->withHeader('link', $this->formatLink($baseUrl, $attribute));
        }

        return $response;
    }

    private function getAttribute(array $attributes, string $requestMethod): ?array
    {
        $attribute = array_values(array_filter($attributes, function (array $attribute): bool {
            return $attribute['deprecationType'] === self::RESOURCE_DEPRECATION_ATTRIBUTE;
        }))[0] ?? null;

        if (null === $attribute) {
            $attribute = array_values(array_filter($attributes, function (array $attr) use ($requestMethod): bool {
                return $attr['deprecationType'] === self::METHOD_DEPRECATION_ATTRIBUTE &&
                    strtoupper($attr['identifier']) === strtoupper($requestMethod);
            }))[0] ?? null;
        }

        return $attribute;
    }

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
     * @throws ReflectionException
     */
    private function getHandler(MiddlewareInterface $routeMiddleware): ?ReflectionClass
    {
        $reflectionHandler = null;
        if ($routeMiddleware instanceof LazyLoadingMiddleware) {
            /** @var class-string $routeMiddlewareName */
            $routeMiddlewareName       = $routeMiddleware->middlewareName;
            $reflectionMiddlewareClass = new ReflectionClass($routeMiddlewareName);
            if ($reflectionMiddlewareClass->implementsInterface(RequestHandlerInterface::class)) {
                $reflectionHandler = $reflectionMiddlewareClass;
            }
        } elseif ($routeMiddleware instanceof MiddlewarePipe) {
            $reflectionClass    = new ReflectionClass($routeMiddleware);
            $middlewarePipeline = $reflectionClass->getProperty('pipeline')->getValue($routeMiddleware);
            for ($middlewarePipeline->rewind(); $middlewarePipeline->valid(); $middlewarePipeline->next()) {
                $reflectionMiddlewareClass = new ReflectionClass($middlewarePipeline->current()->middlewareName);
                if ($reflectionMiddlewareClass->implementsInterface(RequestHandlerInterface::class)) {
                    $reflectionHandler = $reflectionMiddlewareClass;
                }
            }
        } else {
            throw new RuntimeException('Invalid route middleware provided.');
        }

        return $reflectionHandler;
    }

    private function validateAttributes(array $attributes): void
    {
        $intersect = array_intersect(self::DEPRECATION_ATTRIBUTES, array_column($attributes, 'deprecationType'));
        if (count($intersect) === count(self::DEPRECATION_ATTRIBUTES)) {
            throw new DeprecationConflictException(
                sprintf(
                    Message::RESTRICTION_DEPRECATION,
                    self::RESOURCE_DEPRECATION_ATTRIBUTE,
                    self::METHOD_DEPRECATION_ATTRIBUTE
                )
            );
        }
    }

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
