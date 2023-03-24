<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Command\RouteListCommand;
use Mezzio\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class RouteListCommandFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RouteListCommand
    {
        return new RouteListCommand(
            $container->get(Application::class)
        );
    }
}
