<?php

declare(strict_types=1);

namespace Api\App;

use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Application;
use Zend\Expressive\Authentication\OAuth2\TokenEndpointHandler;

/**
 * Class RoutesDelegator
 * @package Api\App
 */
class RoutesDelegator
{
    const REGEXP_UUID = '{uuid:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}';

    /**
     * @param ContainerInterface $container
     * @param $serviceName
     * @param callable $callback
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback)
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
        $app->post('/oauth/generate', [TokenEndpointHandler::class], 'oauth');
        $app->post('/oauth/refresh', [TokenEndpointHandler::class], 'refresh');

        return $app;
    }
}
