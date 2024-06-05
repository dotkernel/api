<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Handler\ErrorReportHandler;
use Api\App\Handler\HomeHandler;
use Api\App\Middleware\ErrorResponseMiddleware;
use Mezzio\Application;
use Mezzio\Authentication\OAuth2\TokenEndpointHandler;
use Psr\Container\ContainerInterface;

use function assert;

class RoutesDelegator
{
    public const REGEXP_UUID = '{uuid:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}';

    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $app = $callback();
        assert($app instanceof Application);

        /**
         * Home page
         */
        $app->get(
            '/',
            HomeHandler::class,
            'home'
        );

        /**
         * OAuth authentication
         */
        $app->post(
            '/security/generate-token',
            [
                ErrorResponseMiddleware::class,
                TokenEndpointHandler::class,
            ],
            'security.generate-token'
        );
        $app->post(
            '/security/refresh-token',
            [
                ErrorResponseMiddleware::class,
                TokenEndpointHandler::class,
            ],
            'security.refresh-token'
        );

        /**
         * Other application reports an error
         */
        $app->post(
            '/error-report',
            [
                ErrorReportHandler::class,
            ],
            'error.report'
        );

        return $app;
    }
}
