<?php

declare(strict_types=1);

namespace App;

use App\Auth\Middleware\AuthMiddleware;
use App\User\Handler\UserAvatarHandler;
use App\User\Handler\UserHandler;
use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Application;
use Zend\Expressive\Authentication\AuthenticationMiddleware;
use Zend\Expressive\Authentication\OAuth2\TokenEndpointHandler;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;

/**
 * Class RoutesDelegator
 * @package App
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

        // Test endpoint
        $app->get('/test', function () {
            return new JsonResponse([
                'message' => 'Welcome to DotKernel API!'
            ]);
        }, 'test');

        // OAuth2 token route
        $app->post('/oauth', [
            BodyParamsMiddleware::class,
            TokenEndpointHandler::class
        ], 'oauth-token');

        $app->get('/users', [
            BodyParamsMiddleware::class,
            AuthenticationMiddleware::class,
            AuthMiddleware::class,
            UserHandler::class
        ], 'users');

        $app->route('/user/' . self::REGEXP_UUID, [
            BodyParamsMiddleware::class,
            AuthenticationMiddleware::class,
            AuthMiddleware::class,
            UserHandler::class
        ], [RequestMethod::METHOD_DELETE, RequestMethod::METHOD_GET, RequestMethod::METHOD_PATCH], 'user');

        $app->post('/user', [
            BodyParamsMiddleware::class,
            UserHandler::class
        ], 'register');

        $app->route('/avatar', [
            BodyParamsMiddleware::class,
            AuthenticationMiddleware::class,
            AuthMiddleware::class,
            UserAvatarHandler::class
        ], [RequestMethod::METHOD_PATCH, RequestMethod::METHOD_POST], 'avatar');

        return $app;
    }
}
