<?php

declare(strict_types=1);

namespace Api\Admin\Factory;

use Api\Admin\Command\AdminCreateCommand;
use Api\Admin\Service\AdminRoleService;
use Api\Admin\Service\AdminService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;

class AdminCreateCommandFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdminCreateCommand
    {
        $adminService = $container->get(AdminService::class);
        assert($adminService instanceof AdminService);

        $adminRoleService = $container->get(AdminRoleService::class);
        assert($adminRoleService instanceof AdminRoleService);

        return new AdminCreateCommand($adminService, $adminRoleService);
    }
}
