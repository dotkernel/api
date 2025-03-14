<?php

declare(strict_types=1);

namespace Api\Security;

use Api\Security\Middleware\ErrorResponseMiddleware;
use Mezzio\Application;
use Mezzio\Authentication\OAuth2\TokenEndpointHandler;
use Psr\Container\ContainerInterface;

use function assert;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $app = $callback();
        assert($app instanceof Application);

        $app->post(
            '/security/token',
            [ErrorResponseMiddleware::class, TokenEndpointHandler::class],
            'security::token'
        );

        return $app;
    }
}
