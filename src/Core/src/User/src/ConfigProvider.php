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
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;

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
                UserAvatarEventListener::class     => AttributedServiceFactory::class,
                UserAvatarRepository::class        => AttributedRepositoryFactory::class,
                UserDetailRepository::class        => AttributedRepositoryFactory::class,
                UserRepository::class              => AttributedRepositoryFactory::class,
                UserResetPasswordRepository::class => AttributedRepositoryFactory::class,
                UserRoleRepository::class          => AttributedRepositoryFactory::class,
            ],
        ];
    }

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
