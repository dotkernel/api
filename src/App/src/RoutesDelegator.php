<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Handler\GetIndexResourceHandler;
use Api\App\Handler\PostErrorReportResourceHandler;
use Api\App\Middleware\ErrorReportPermissionMiddleware;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

use function assert;

class RoutesDelegator
{
    public const REGEXP_UUID = '{uuid:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}';

    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $app = $callback();
        assert($app instanceof Application);

        // Home page
        $app->get('/', GetIndexResourceHandler::class, 'app::view-index');

        // Other application reports an error
        $app->post(
            '/error-report',
            [ErrorReportPermissionMiddleware::class, PostErrorReportResourceHandler::class],
            'app::create-error-report'
        );

        return $app;
    }
}
