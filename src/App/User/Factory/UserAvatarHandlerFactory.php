<?php

declare(strict_types=1);

namespace App\User\Factory;

use App\User\Form\UserAvatarInputFilter;
use App\User\Handler\UserAvatarHandler;
use App\User\Service\UserService;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

/**
 * Class UserAvatarHandlerFactory
 * @package App\User\Factory
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
            $container->get(UserService::class),
            $container->get(UserAvatarInputFilter::class)
        );
    }
}
