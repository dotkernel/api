<?php

declare(strict_types=1);

namespace Core\Admin;

use Core\Admin\DBAL\Types\AdminStatusEnumType;
use Core\Admin\Repository\AdminRepository;
use Core\Admin\Repository\AdminRoleRepository;
use Core\Admin\Service\AdminRoleService;
use Core\Admin\Service\AdminRoleServiceInterface;
use Core\Admin\Service\AdminService;
use Core\Admin\Service\AdminServiceInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;

use function getcwd;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'doctrine'     => $this->getDoctrineConfig(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'factories' => [
                AdminService::class        => AttributedServiceFactory::class,
                AdminRoleService::class    => AttributedServiceFactory::class,
                AdminRepository::class     => AttributedRepositoryFactory::class,
                AdminRoleRepository::class => AttributedRepositoryFactory::class,
            ],
            'aliases'   => [
                AdminServiceInterface::class     => AdminService::class,
                AdminRoleServiceInterface::class => AdminRoleService::class,
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'driver' => [
                'orm_default'   => [
                    'drivers' => [
                        'Core\Admin\Entity' => 'AdminEntities',
                    ],
                ],
                'AdminEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => getcwd() . '/src/Core/src/Admin/src/Entity',
                ],
            ],
            'types'  => [
                AdminStatusEnumType::NAME => AdminStatusEnumType::class,
            ],
        ];
    }
}
