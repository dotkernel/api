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
use Api\User\Service\UserRoleService;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Factory\AnnotatedServiceFactory;
use Mezzio\Hal\Metadata\MetadataMap;

/**
 * Class ConfigProvider
 * @package Api\User
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig(),
            'templates' => $this->getTemplates()
        ];
    }

    /**
     * @return array
     */
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
            ]
        ];
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'user' => [__DIR__ . '/../templates/user']
            ]
        ];
    }
}
