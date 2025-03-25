<?php

declare(strict_types=1);

namespace Api\Security;

use Api\Security\Middleware\ErrorResponseMiddleware;
use Dot\Router\RouteCollectorInterface;
use Mezzio\Application;
use Mezzio\Authentication\OAuth2\TokenEndpointHandler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class RoutesDelegator
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var RouteCollectorInterface $routeCollector */
        $routeCollector = $container->get(RouteCollectorInterface::class);

        $routeCollector->group('/security', ErrorResponseMiddleware::class)
            ->post('/token', TokenEndpointHandler::class, 'security::token');

        return $callback();
    }
}
