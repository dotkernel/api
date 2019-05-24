<?php

declare(strict_types=1);

namespace App\Auth\Factory;

use App\Auth\Middleware\AuthMiddleware;
use App\User\Service\UserService;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Authorization\AuthorizationInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class IdentityMiddlewareFactory
 * @package App\User\Factory
 */
class AuthMiddlewareFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AuthMiddleware
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AuthMiddleware
    {
        return new AuthMiddleware(
            $container->get(UserService::class),
            $container->get(AuthorizationInterface::class)
        );
    }
}
