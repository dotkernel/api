<?php

declare(strict_types=1);

namespace App\User\Factory;

use App\User\Form\UserCreateInputFilter;
use App\User\Form\UserUpdateInputFilter;
use App\User\Handler\UserHandler;
use App\User\Service\UserService;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

/**
 * Class UserListHandlerFactory
 * @package App\User\Factory
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
            $container->get(UserService::class),
            $container->get(UserCreateInputFilter::class),
            $container->get(UserUpdateInputFilter::class)
        );
    }
}
