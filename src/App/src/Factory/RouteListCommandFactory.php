<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Command\RouteListCommand;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

/**
 * Class RouteListCommandFactory
 * @package Api\App\Factory
 */
class RouteListCommandFactory
{
    /**
     * @param ContainerInterface $container
     * @return RouteListCommand
     */
    public function __invoke(ContainerInterface $container): RouteListCommand
    {
        return new RouteListCommand(
            $container->get(Application::class)
        );
    }
}
