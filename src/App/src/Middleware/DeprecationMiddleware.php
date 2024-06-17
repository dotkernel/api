<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\App\Attribute\MethodDeprecation;
use Api\App\Attribute\ResourceDeprecation;
use Api\App\Exception\DeprecationConflictException;
use Api\App\Handler\ResponseTrait;
use Api\App\Message;
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

use function array_key_exists;
use function array_keys;
use function sprintf;

class DeprecationMiddleware implements MiddlewareInterface
{
    use ResponseTrait;

    public const RESOURCE_DEPRECATION_ATTRIBUTE = ResourceDeprecation::class;
    public const METHOD_DEPRECATION_ATTRIBUTE   = MethodDeprecation::class;

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

        $reflectionHandler = null;
        $matchedRoute      = $routeResult->getMatchedRoute();
        if (! $matchedRoute) {
            return $response;
        }

        $routeMiddleware = $matchedRoute->getMiddleware();
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
        }

        if (! $reflectionHandler) {
            return $response;
        }

        $attributes = $this->getAttributes($reflectionHandler, self::RESOURCE_DEPRECATION_ATTRIBUTE);
        foreach ($reflectionHandler->getMethods() as $method) {
            $methodRef   = new ReflectionMethod($method->class, $method->name);
            $attributes += $this->getAttributes($methodRef, self::METHOD_DEPRECATION_ATTRIBUTE);
        }

        if ([self::RESOURCE_DEPRECATION_ATTRIBUTE, self::METHOD_DEPRECATION_ATTRIBUTE] === array_keys($attributes)) {
            throw new DeprecationConflictException(
                sprintf(
                    Message::RESTRICTION_DEPRECATION,
                    self::RESOURCE_DEPRECATION_ATTRIBUTE,
                    self::METHOD_DEPRECATION_ATTRIBUTE
                )
            );
        }

        $sunset = null;
        $link   = null;
        if (array_key_exists(self::RESOURCE_DEPRECATION_ATTRIBUTE, $attributes)) {
            $sunset = $attributes[self::RESOURCE_DEPRECATION_ATTRIBUTE]['sunset'];
            $link   = $attributes[self::RESOURCE_DEPRECATION_ATTRIBUTE]['link'];
        }

        if (array_key_exists(self::METHOD_DEPRECATION_ATTRIBUTE, $attributes)) {
            $sunset = $attributes[self::METHOD_DEPRECATION_ATTRIBUTE]['sunset'];
            $link   = $attributes[self::METHOD_DEPRECATION_ATTRIBUTE]['link'];
        }

        if ($sunset !== null) {
            $response = $response->withHeader('sunset', $sunset);
        }

        if ($link !== null) {
            $response = $response->withHeader('link', $link);
        }

        return $response;
    }

    /**
     * @param class-string $type
     */
    public function getAttributes(ReflectionClass|ReflectionMethod $reflection, string $type): array
    {
        $attributes = [];
        foreach ($reflection->getAttributes($type) as $attribute) {
            $attribute->newInstance();
            $attributes[$attribute->getName()] = $attribute->getArguments();
        }

        return $attributes;
    }
}
