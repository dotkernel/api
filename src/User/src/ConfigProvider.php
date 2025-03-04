<?php

declare(strict_types=1);

namespace Api\User;

use Api\App\ConfigProvider as AppConfigProvider;
use Api\App\Factory\HandlerDelegatorFactory;
use Api\User\Collection\UserCollection;
use Api\User\Collection\UserRoleCollection;
use Api\User\Entity\User;
use Api\User\Entity\UserAvatar;
use Api\User\Entity\UserRole;
use Api\User\EventListener\UserAvatarEventListener;
use Api\User\Handler\AccountActivateHandler;
use Api\User\Handler\AccountAvatarHandler;
use Api\User\Handler\AccountHandler;
use Api\User\Handler\AccountRecoveryHandler;
use Api\User\Handler\AccountResetPasswordHandler;
use Api\User\Handler\UserActivateHandler;
use Api\User\Handler\UserAvatarHandler;
use Api\User\Handler\UserCollectionHandler;
use Api\User\Handler\UserHandler;
use Api\User\Handler\UserRoleCollectionHandler;
use Api\User\Handler\UserRoleHandler;
use Api\User\Repository\UserAvatarRepository;
use Api\User\Repository\UserDetailRepository;
use Api\User\Repository\UserRepository;
use Api\User\Repository\UserResetPasswordRepository;
use Api\User\Repository\UserRoleRepository;
use Api\User\Service\UserAvatarService;
use Api\User\Service\UserAvatarServiceInterface;
use Api\User\Service\UserRoleService;
use Api\User\Service\UserRoleServiceInterface;
use Api\User\Service\UserService;
use Api\User\Service\UserServiceInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'     => $this->getDependencies(),
            'doctrine'         => $this->getDoctrineConfig(),
            MetadataMap::class => $this->getHalConfig(),
            'templates'        => $this->getTemplates(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class                 => [RoutesDelegator::class],
                AccountActivateHandler::class      => [HandlerDelegatorFactory::class],
                AccountAvatarHandler::class        => [HandlerDelegatorFactory::class],
                AccountHandler::class              => [HandlerDelegatorFactory::class],
                AccountRecoveryHandler::class      => [HandlerDelegatorFactory::class],
                AccountResetPasswordHandler::class => [HandlerDelegatorFactory::class],
                UserActivateHandler::class         => [HandlerDelegatorFactory::class],
                UserAvatarHandler::class           => [HandlerDelegatorFactory::class],
                UserCollectionHandler::class       => [HandlerDelegatorFactory::class],
                UserHandler::class                 => [HandlerDelegatorFactory::class],
                UserRoleCollectionHandler::class   => [HandlerDelegatorFactory::class],
                UserRoleHandler::class             => [HandlerDelegatorFactory::class],
            ],
            'factories'  => [
                AccountActivateHandler::class      => AttributedServiceFactory::class,
                AccountAvatarHandler::class        => AttributedServiceFactory::class,
                AccountHandler::class              => AttributedServiceFactory::class,
                AccountRecoveryHandler::class      => AttributedServiceFactory::class,
                AccountResetPasswordHandler::class => AttributedServiceFactory::class,
                UserActivateHandler::class         => AttributedServiceFactory::class,
                UserAvatarEventListener::class     => AttributedServiceFactory::class,
                UserAvatarHandler::class           => AttributedServiceFactory::class,
                UserCollectionHandler::class       => AttributedServiceFactory::class,
                UserHandler::class                 => AttributedServiceFactory::class,
                UserRoleCollectionHandler::class   => AttributedServiceFactory::class,
                UserRoleHandler::class             => AttributedServiceFactory::class,
                UserService::class                 => AttributedServiceFactory::class,
                UserRoleService::class             => AttributedServiceFactory::class,
                UserAvatarService::class           => AttributedServiceFactory::class,
                UserAvatarRepository::class        => AttributedRepositoryFactory::class,
                UserDetailRepository::class        => AttributedRepositoryFactory::class,
                UserRepository::class              => AttributedRepositoryFactory::class,
                UserResetPasswordRepository::class => AttributedRepositoryFactory::class,
                UserRoleRepository::class          => AttributedRepositoryFactory::class,
            ],
            'aliases'    => [
                UserAvatarServiceInterface::class => UserAvatarService::class,
                UserRoleServiceInterface::class   => UserRoleService::class,
                UserServiceInterface::class       => UserService::class,
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'driver' => [
                'orm_default'  => [
                    'drivers' => [
                        'Api\User\Entity' => 'UserEntities',
                    ],
                ],
                'UserEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => __DIR__ . '/Entity',
                ],
            ],
        ];
    }

    public function getHalConfig(): array
    {
        return [
            AppConfigProvider::getCollection(UserCollection::class, 'user.list', 'users'),
            AppConfigProvider::getCollection(UserRoleCollection::class, 'user.role.list', 'roles'),
            AppConfigProvider::getResource(User::class, 'user.view'),
            AppConfigProvider::getResource(UserRole::class, 'user.role.view'),
            AppConfigProvider::getResource(UserAvatar::class, 'user.avatar.view'),
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'user' => [
                    __DIR__ . '/../templates/user',
                ],
            ],
        ];
    }
}
