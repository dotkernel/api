<?php

declare(strict_types=1);

namespace Core\Admin;

use Core\Admin\DBAL\Types\AdminRoleEnumType;
use Core\Admin\DBAL\Types\AdminStatusEnumType;
use Core\Admin\Repository\AdminLoginRepository;
use Core\Admin\Repository\AdminRepository;
use Core\Admin\Repository\AdminRoleRepository;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;

/**
 * @phpstan-type ConfigType array{
 *      dependencies: DependenciesType,
 *      doctrine: DoctrineConfigType,
 * }
 * @phpstan-type DoctrineConfigType array{
 *      driver: array{
 *          orm_default: array{
 *              drivers: array<non-empty-string, non-empty-string>,
 *          },
 *          AdminEntities: array{
 *              class: class-string<MappingDriver>,
 *              cache: non-empty-string,
 *              paths: non-empty-string[],
 *          },
 *      },
 *      types: array<non-empty-string, class-string>,
 * }
 * @phpstan-type DependenciesType array{
 *       factories: array<class-string, class-string>,
 * }
 */
class ConfigProvider
{
    /**
     * @return ConfigType
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'doctrine'     => $this->getDoctrineConfig(),
        ];
    }

    /**
     * @return DependenciesType
     */
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

    /**
     * @return DoctrineConfigType
     */
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
