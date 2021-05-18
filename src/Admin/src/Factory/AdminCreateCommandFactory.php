<?php

declare(strict_types=1);

namespace Api\Admin\Factory;

use Api\Admin\Command\AdminCreateCommand;
use Api\Admin\Service\AdminRoleService;
use Api\Admin\Service\AdminService;
use Psr\Container\ContainerInterface;

/**
 * Class AdminCreateCommandFactory
 * @package Api\Admin\Factory
 */
class AdminCreateCommandFactory
{
    /**
     * @param ContainerInterface $container
     * @return AdminCreateCommand
     */
    public function __invoke(ContainerInterface $container): AdminCreateCommand
    {
        return new AdminCreateCommand(
            $container->get(AdminService::class),
            $container->get(AdminRoleService::class)
        );
    }
}
