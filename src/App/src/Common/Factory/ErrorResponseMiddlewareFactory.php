<?php


namespace Api\App\Common\Factory;


use Api\App\Common\Middleware\ErrorResponseMiddleware;
use Psr\Container\ContainerInterface;

class ErrorResponseMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return ErrorResponseMiddleware
     */
    public function __invoke(ContainerInterface $container): ErrorResponseMiddleware
    {
        return new ErrorResponseMiddleware($container->get('config')['authentication']);
    }
}