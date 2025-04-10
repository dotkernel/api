<?php

declare(strict_types=1);

namespace Core\Admin;

use Core\Admin\DBAL\Types\AdminRoleEnumType;
use Core\Admin\DBAL\Types\AdminStatusEnumType;
use Core\Admin\Repository\AdminLoginRepository;
use Core\Admin\Repository\AdminRepository;
use Core\Admin\Repository\AdminRoleRepository;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;

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
                AdminRepository::class      => AttributedRepositoryFactory::class,
                AdminLoginRepository::class => AttributedRepositoryFactory::class,
                AdminRoleRepository::class  => AttributedRepositoryFactory::class,
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
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
            'types'  => [
                AdminRoleEnumType::NAME   => AdminRoleEnumType::class,
                AdminStatusEnumType::NAME => AdminStatusEnumType::class,
            ],
        ];
    }
}
