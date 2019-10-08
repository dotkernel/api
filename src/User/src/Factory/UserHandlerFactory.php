<?php

declare(strict_types=1);

namespace Api\User\Factory;

use Api\User\Handler\UserHandler;
use Api\User\Service\UserService;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

/**
 * Class UserListHandlerFactory
 * @package Api\User\Factory
 */
class UserHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return UserHandler
     */
    public function __invoke(ContainerInterface $container) : UserHandler
    {
        return new UserHandler(
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $container->get(UserService::class)
        );
    }
}
