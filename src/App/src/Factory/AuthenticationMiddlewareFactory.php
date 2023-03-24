<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Middleware\AuthenticationMiddleware;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AuthenticationMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AuthenticationMiddleware
    {
        if (!$container->has(AuthenticationInterface::class)) {
            throw new InvalidConfigException('AuthenticationInterface service is missing');
        }

        return new AuthenticationMiddleware(
            $container->get(AuthenticationInterface::class)
        );
    }
}
