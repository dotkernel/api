<?php

declare(strict_types=1);

namespace Core\User;

use Core\User\DBAL\Types\UserResetPasswordStatusEnumType;
use Core\User\DBAL\Types\UserRoleEnumType;
use Core\User\DBAL\Types\UserStatusEnumType;
use Core\User\EventListener\UserAvatarEventListener;
use Core\User\Repository\UserAvatarRepository;
use Core\User\Repository\UserDetailRepository;
use Core\User\Repository\UserRepository;
use Core\User\Repository\UserResetPasswordRepository;
use Core\User\Repository\UserRoleRepository;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;

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
 *          UserEntities: array{
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
                UserAvatarEventListener::class     => AttributedServiceFactory::class,
                UserAvatarRepository::class        => AttributedRepositoryFactory::class,
                UserDetailRepository::class        => AttributedRepositoryFactory::class,
                UserRepository::class              => AttributedRepositoryFactory::class,
                UserResetPasswordRepository::class => AttributedRepositoryFactory::class,
                UserRoleRepository::class          => AttributedRepositoryFactory::class,
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
                'orm_default'  => [
                    'drivers' => [
                        'Core\User\Entity' => 'UserEntities',
                    ],
                ],
                'UserEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
            'types'  => [
                UserRoleEnumType::NAME                => UserRoleEnumType::class,
                UserStatusEnumType::NAME              => UserStatusEnumType::class,
                UserResetPasswordStatusEnumType::NAME => UserResetPasswordStatusEnumType::class,
            ],
        ];
    }
}
