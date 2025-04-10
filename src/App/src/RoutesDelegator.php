<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Handler\GetIndexResourceHandler;
use Api\App\Handler\PostErrorReportResourceHandler;
use Api\App\Middleware\ErrorReportPermissionMiddleware;
use Dot\Router\RouteCollectorInterface;
use Mezzio\Application;
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

        $routeCollector
            ->get('/', GetIndexResourceHandler::class, 'app::view-index')
            ->post(
                '/error-report',
                [ErrorReportPermissionMiddleware::class, PostErrorReportResourceHandler::class],
                'app::create-error-report'
            );

        return $callback();
    }
}
