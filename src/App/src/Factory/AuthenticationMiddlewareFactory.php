<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Middleware\AuthenticationMiddleware;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

/**
 * Class AuthenticationMiddlewareFactory
 * @package Api\App\Factory
 */
class AuthenticationMiddlewareFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthenticationMiddleware
     */
    public function __invoke(ContainerInterface $container): AuthenticationMiddleware
    {
        $authentication = $container->has(AuthenticationInterface::class)
            ? $container->get(AuthenticationInterface::class)
            : null;
        Assert::nullOrIsInstanceOf($authentication, AuthenticationInterface::class);

        if (null === $authentication) {
            throw new InvalidConfigException(
                'AuthenticationInterface service is missing'
            );
        }

        return new AuthenticationMiddleware($authentication);
    }
}
