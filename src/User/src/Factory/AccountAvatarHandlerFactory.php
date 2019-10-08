<?php

declare(strict_types=1);

namespace Api\User\Factory;

use Api\User\Handler\AccountAvatarHandler;
use Api\User\Service\UserService;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

/**
 * Class AccountAvatarHandlerFactory
 * @package Api\User\Factory
 */
class AccountAvatarHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AccountAvatarHandler
     */
    public function __invoke(ContainerInterface $container) : AccountAvatarHandler
    {
        return new AccountAvatarHandler(
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $container->get(UserService::class)
        );
    }
}
