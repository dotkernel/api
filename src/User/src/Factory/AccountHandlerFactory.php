<?php

declare(strict_types=1);

namespace Api\User\Factory;

use Api\User\Handler\AccountHandler;
use Api\User\Service\UserService;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

/**
 * Class AccountHandlerFactory
 * @package Api\User\Factory
 */
class AccountHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AccountHandler
     */
    public function __invoke(ContainerInterface $container) : AccountHandler
    {
        return new AccountHandler(
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $container->get(UserService::class)
        );
    }
}
