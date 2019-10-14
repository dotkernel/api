<?php

declare(strict_types=1);

namespace Api\User\Factory;

use Api\User\Handler\AccountResetPasswordHandler;
use Api\User\Service\UserService;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

/**
 * Class AccountResetPasswordHandlerFactory
 * @package Api\User\Factory
 */
class AccountResetPasswordHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AccountResetPasswordHandler
     */
    public function __invoke(ContainerInterface $container) : AccountResetPasswordHandler
    {
        return new AccountResetPasswordHandler(
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $container->get(UserService::class)
        );
    }
}
