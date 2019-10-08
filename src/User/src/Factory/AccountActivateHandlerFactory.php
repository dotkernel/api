<?php

declare(strict_types=1);

namespace Api\User\Factory;

use Api\User\Handler\AccountActivateHandler;
use Api\User\Service\UserService;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;
use Zend\Expressive\Helper\UrlHelper;

/**
 * Class AccountActivateHandlerFactory
 * @package Api\User\Factory
 */
class AccountActivateHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return AccountActivateHandler
     */
    public function __invoke(ContainerInterface $container) : AccountActivateHandler
    {
        return new AccountActivateHandler(
            $container->get(HalResponseFactory::class),
            $container->get(ResourceGenerator::class),
            $container->get(UserService::class),
            $container->get(UrlHelper::class),
        );
    }
}
