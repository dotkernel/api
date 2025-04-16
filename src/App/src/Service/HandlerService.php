<?php

declare(strict_types=1);

namespace Api\App\Service;

use Api\App\Exception\RuntimeException;
use Dot\Router\Middleware\LazyLoadingMiddleware;
use Laminas\Stratigility\MiddlewarePipe;
use Mezzio\Middleware\LazyLoadingMiddleware as MezzioLazyLoadingMiddleware;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionException;

class HandlerService
{
    /**
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public static function getReflectionHandler(ServerRequestInterface $request): ?ReflectionClass
    {
        $routeResult = $request->getAttribute(RouteResult::class);
        if (! $routeResult instanceof RouteResult || $routeResult->isFailure()) {
            return null;
        }

        $matchedRoute = $routeResult->getMatchedRoute();
        if (! $matchedRoute instanceof Route) {
            return null;
        }

        $routeMiddleware = $matchedRoute->getMiddleware();
        if (
            $routeMiddleware instanceof MezzioLazyLoadingMiddleware
            || $routeMiddleware instanceof LazyLoadingMiddleware
        ) {
            /** @var class-string $routeMiddlewareName */
            $routeMiddlewareName       = $routeMiddleware->middlewareName;
            $reflectionMiddlewareClass = new ReflectionClass($routeMiddlewareName);
            if ($reflectionMiddlewareClass->implementsInterface(RequestHandlerInterface::class)) {
                return $reflectionMiddlewareClass;
            }
        } elseif ($routeMiddleware instanceof MiddlewarePipe) {
            $reflectionClass    = new ReflectionClass($routeMiddleware);
            $middlewarePipeline = $reflectionClass->getProperty('pipeline')->getValue($routeMiddleware);
            for ($middlewarePipeline->rewind(); $middlewarePipeline->valid(); $middlewarePipeline->next()) {
                $reflectionMiddlewareClass = new ReflectionClass($middlewarePipeline->current()->middlewareName);
                if ($reflectionMiddlewareClass->implementsInterface(RequestHandlerInterface::class)) {
                    return $reflectionMiddlewareClass;
                }
            }
        } else {
            throw RuntimeException::create('Invalid route middleware provided.');
        }

        return null;
    }
}
