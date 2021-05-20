<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Middleware\ErrorResponseMiddleware;
use Api\App\Log\Handler\ErrorReportHandler;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Application;
use Mezzio\Authentication\OAuth2\TokenEndpointHandler;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Psr\Container\ContainerInterface;

/**
 * Class RoutesDelegator
 * @package Api\App
 */
class RoutesDelegator
{
    public const REGEXP_UUID = '{uuid:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}';

    /**
     * @param ContainerInterface $container
     * @param $serviceName
     * @param callable $callback
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        /**
         * Home page
         */
        $app->get('/', function () {
            return new JsonResponse(['message' => 'Welcome to DotKernel API!']);
        }, 'home');

        /**
         * OAuth authentication
         */
        $app->post('/security/generate-token', [
            ErrorResponseMiddleware::class,
            TokenEndpointHandler::class
        ], 'security.generate-token');
        $app->post('/security/refresh-token', [
            ErrorResponseMiddleware::class,
            TokenEndpointHandler::class
        ], 'security.refresh-token');

        /**
         * Other application reports an error
         */
        $app->post('/error-report', [
            BodyParamsMiddleware::class,
            ErrorReportHandler::class
        ], 'error.report');

        return $app;
    }
}
