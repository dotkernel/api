<?php

declare(strict_types=1);

namespace Api\User;

use Api\App\ConfigProvider as AppConfigProvider;
use Api\App\Factory\HandlerDelegatorFactory;
use Api\User\Collection\UserCollection;
use Api\User\Collection\UserRoleCollection;
use Api\User\Handler\Account\Avatar\DeleteUserAccountAvatarResourceHandler;
use Api\User\Handler\Account\Avatar\GetUserAccountAvatarResourceHandler;
use Api\User\Handler\Account\Avatar\PostUserAccountAvatarResourceHandler;
use Api\User\Handler\Account\DeleteUserAccountResourceHandler;
use Api\User\Handler\Account\GetUserAccountResourceHandler;
use Api\User\Handler\Account\PatchUserAccountActivateHandler;
use Api\User\Handler\Account\PatchUserAccountResourceHandler;
use Api\User\Handler\Account\PostUserAccountActivateHandler;
use Api\User\Handler\Account\PostUserAccountRecoverHandler;
use Api\User\Handler\Account\PostUserAccountResourceHandler;
use Api\User\Handler\Account\ResetPassword\GetUserAccountResetPasswordResourceHandler;
use Api\User\Handler\Account\ResetPassword\PatchUserAccountResetPasswordResourceHandler;
use Api\User\Handler\Account\ResetPassword\PostUserAccountResetPasswordResourceHandler;
use Api\User\Handler\User\Avatar\DeleteUserAvatarResourceHandler;
use Api\User\Handler\User\Avatar\GetUserAvatarResourceHandler;
use Api\User\Handler\User\Avatar\PostUserAvatarResourceHandler;
use Api\User\Handler\User\DeleteUserResourceHandler;
use Api\User\Handler\User\GetUserCollectionHandler;
use Api\User\Handler\User\GetUserResourceHandler;
use Api\User\Handler\User\PatchUserActivateHandler;
use Api\User\Handler\User\PatchUserDeactivateHandler;
use Api\User\Handler\User\PatchUserResourceHandler;
use Api\User\Handler\User\PostUserResourceHandler;
use Api\User\Handler\User\Role\GetUserRoleCollectionHandler;
use Api\User\Handler\User\Role\GetUserRoleResourceHandler;
use Api\User\Service\UserAvatarService;
use Api\User\Service\UserAvatarServiceInterface;
use Api\User\Service\UserResetPasswordService;
use Api\User\Service\UserResetPasswordServiceInterface;
use Api\User\Service\UserRoleService;
use Api\User\Service\UserRoleServiceInterface;
use Api\User\Service\UserService;
use Api\User\Service\UserServiceInterface;
use Core\User\Entity\User;
use Core\User\Entity\UserAvatar;
use Core\User\Entity\UserRole;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'     => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig(),
            'templates'        => $this->getTemplates(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class                                  => [RoutesDelegator::class],
                DeleteUserAccountAvatarResourceHandler::class       => [HandlerDelegatorFactory::class],
                DeleteUserAccountResourceHandler::class             => [HandlerDelegatorFactory::class],
                DeleteUserAvatarResourceHandler::class              => [HandlerDelegatorFactory::class],
                DeleteUserResourceHandler::class                    => [HandlerDelegatorFactory::class],
                GetUserAccountAvatarResourceHandler::class          => [HandlerDelegatorFactory::class],
                GetUserAccountResetPasswordResourceHandler::class   => [HandlerDelegatorFactory::class],
                GetUserAccountResourceHandler::class                => [HandlerDelegatorFactory::class],
                GetUserAvatarResourceHandler::class                 => [HandlerDelegatorFactory::class],
                GetUserCollectionHandler::class                     => [HandlerDelegatorFactory::class],
                GetUserResourceHandler::class                       => [HandlerDelegatorFactory::class],
                GetUserRoleCollectionHandler::class                 => [HandlerDelegatorFactory::class],
                GetUserRoleResourceHandler::class                   => [HandlerDelegatorFactory::class],
                PatchUserAccountActivateHandler::class              => [HandlerDelegatorFactory::class],
                PatchUserAccountResetPasswordResourceHandler::class => [HandlerDelegatorFactory::class],
                PatchUserAccountResourceHandler::class              => [HandlerDelegatorFactory::class],
                PatchUserActivateHandler::class                     => [HandlerDelegatorFactory::class],
                PatchUserDeactivateHandler::class                   => [HandlerDelegatorFactory::class],
                PatchUserResourceHandler::class                     => [HandlerDelegatorFactory::class],
                PostUserAccountActivateHandler::class               => [HandlerDelegatorFactory::class],
                PostUserAccountAvatarResourceHandler::class         => [HandlerDelegatorFactory::class],
                PostUserAccountRecoverHandler::class                => [HandlerDelegatorFactory::class],
                PostUserAccountResetPasswordResourceHandler::class  => [HandlerDelegatorFactory::class],
                PostUserAccountResourceHandler::class               => [HandlerDelegatorFactory::class],
                PostUserAvatarResourceHandler::class                => [HandlerDelegatorFactory::class],
                PostUserResourceHandler::class                      => [HandlerDelegatorFactory::class],
            ],
            'factories'  => [
                DeleteUserAccountAvatarResourceHandler::class       => AttributedServiceFactory::class,
                DeleteUserAccountResourceHandler::class             => AttributedServiceFactory::class,
                DeleteUserAvatarResourceHandler::class              => AttributedServiceFactory::class,
                DeleteUserResourceHandler::class                    => AttributedServiceFactory::class,
                GetUserAccountAvatarResourceHandler::class          => AttributedServiceFactory::class,
                GetUserAccountResetPasswordResourceHandler::class   => AttributedServiceFactory::class,
                GetUserAccountResourceHandler::class                => AttributedServiceFactory::class,
                GetUserAvatarResourceHandler::class                 => AttributedServiceFactory::class,
                GetUserCollectionHandler::class                     => AttributedServiceFactory::class,
                GetUserResourceHandler::class                       => AttributedServiceFactory::class,
                GetUserRoleCollectionHandler::class                 => AttributedServiceFactory::class,
                GetUserRoleResourceHandler::class                   => AttributedServiceFactory::class,
                PatchUserAccountActivateHandler::class              => AttributedServiceFactory::class,
                PatchUserAccountResetPasswordResourceHandler::class => AttributedServiceFactory::class,
                PatchUserAccountResourceHandler::class              => AttributedServiceFactory::class,
                PatchUserActivateHandler::class                     => AttributedServiceFactory::class,
                PatchUserDeactivateHandler::class                   => AttributedServiceFactory::class,
                PatchUserResourceHandler::class                     => AttributedServiceFactory::class,
                PostUserAccountActivateHandler::class               => AttributedServiceFactory::class,
                PostUserAccountAvatarResourceHandler::class         => AttributedServiceFactory::class,
                PostUserAccountRecoverHandler::class                => AttributedServiceFactory::class,
                PostUserAccountResetPasswordResourceHandler::class  => AttributedServiceFactory::class,
                PostUserAccountResourceHandler::class               => AttributedServiceFactory::class,
                PostUserAvatarResourceHandler::class                => AttributedServiceFactory::class,
                PostUserResourceHandler::class                      => AttributedServiceFactory::class,
                UserAvatarService::class                            => AttributedServiceFactory::class,
                UserResetPasswordService::class                     => AttributedServiceFactory::class,
                UserRoleService::class                              => AttributedServiceFactory::class,
                UserService::class                                  => AttributedServiceFactory::class,
            ],
            'aliases'    => [
                UserAvatarServiceInterface::class        => UserAvatarService::class,
                UserResetPasswordServiceInterface::class => UserResetPasswordService::class,
                UserRoleServiceInterface::class          => UserRoleService::class,
                UserServiceInterface::class              => UserService::class,
            ],
        ];
    }

    private function getHalConfig(): array
    {
        return [
            AppConfigProvider::getCollection(UserCollection::class, 'user::list-user', 'users'),
            AppConfigProvider::getCollection(UserRoleCollection::class, 'user::list-role', 'roles'),
            AppConfigProvider::getResource(User::class, 'user::view-user'),
            AppConfigProvider::getResource(UserRole::class, 'user::view-role'),
            AppConfigProvider::getResource(UserAvatar::class, 'user::view-user-avatar'),
        ];
    }

    private function getTemplates(): array
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
