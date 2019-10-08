<?php

declare(strict_types=1);

namespace Api\User\Factory;

use Api\User\Handler\UserAvatarHandler;
use Api\User\Service\UserService;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

/**
 * Class UserAvatarHandlerFactory
 * @package Api\User\Factory
 */
class UserAvatarHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return UserAvatarHandler
     */
    public function __invoke(ContainerInterface $container) : UserAvatarHandler
    {
        return new UserAvatarHandler(
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $container->get(UserService::class)
        );
    }
}
