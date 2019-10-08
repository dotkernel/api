<?php

declare(strict_types=1);

namespace Api\User\Factory;

use Api\User\Handler\UserActivateHandler;
use Api\User\Service\UserService;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

/**
 * Class UserActivateHandlerFactory
 * @package Api\User\Factory
 */
class UserActivateHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return UserActivateHandler
     */
    public function __invoke(ContainerInterface $container) : UserActivateHandler
    {
        return new UserActivateHandler(
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $container->get(UserService::class)
        );
    }
}
