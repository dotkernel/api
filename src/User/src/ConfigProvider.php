<?php

declare(strict_types=1);

namespace Api\User;

use Api\App\ConfigProvider as AppConfigProvider;
use Api\User\Collection\UserCollection;
use Api\User\Collection\UserRoleCollection;
use Api\User\Entity\User;
use Api\User\Entity\UserAvatar;
use Api\User\Entity\UserRole;
use Api\User\Handler\AccountActivateHandler;
use Api\User\Handler\AccountAvatarHandler;
use Api\User\Handler\AccountHandler;
use Api\User\Handler\AccountRecoveryHandler;
use Api\User\Handler\AccountResetPasswordHandler;
use Api\User\Handler\UserActivateHandler;
use Api\User\Handler\UserAvatarHandler;
use Api\User\Handler\UserHandler;
use Api\User\Handler\UserRoleHandler;
use Api\User\Repository\UserAvatarRepository;
use Api\User\Repository\UserDetailRepository;
use Api\User\Repository\UserRepository;
use Api\User\Repository\UserRoleRepository;
use Api\User\Service\UserAvatarService;
use Api\User\Service\UserAvatarServiceInterface;
use Api\User\Service\UserRoleService;
use Api\User\Service\UserRoleServiceInterface;
use Api\User\Service\UserService;
use Api\User\Service\UserServiceInterface;
use Dot\AnnotatedServices\Factory\AnnotatedRepositoryFactory;
use Dot\AnnotatedServices\Factory\AnnotatedServiceFactory;
use Mezzio\Hal\Metadata\MetadataMap;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig(),
            'templates' => $this->getTemplates()
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                AccountActivateHandler::class => AnnotatedServiceFactory::class,
                AccountAvatarHandler::class => AnnotatedServiceFactory::class,
                AccountHandler::class => AnnotatedServiceFactory::class,
                AccountResetPasswordHandler::class => AnnotatedServiceFactory::class,
                AccountRecoveryHandler::class => AnnotatedServiceFactory::class,
                UserActivateHandler::class => AnnotatedServiceFactory::class,
                UserAvatarHandler::class => AnnotatedServiceFactory::class,
                UserHandler::class => AnnotatedServiceFactory::class,
                UserRoleHandler::class => AnnotatedServiceFactory::class,
                UserService::class => AnnotatedServiceFactory::class,
                UserRoleService::class => AnnotatedServiceFactory::class,
                UserAvatarService::class => AnnotatedServiceFactory::class,
                UserRepository::class => AnnotatedRepositoryFactory::class,
                UserDetailRepository::class => AnnotatedRepositoryFactory::class,
                UserRoleRepository::class => AnnotatedRepositoryFactory::class,
                UserAvatarRepository::class => AnnotatedRepositoryFactory::class,
            ],
            'aliases' => [
                UserAvatarServiceInterface::class => UserAvatarService::class,
                UserRoleServiceInterface::class => UserRoleService::class,
                UserServiceInterface::class => UserService::class,
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
                'user' => [__DIR__ . '/../templates/user']
            ]
        ];
    }
}
