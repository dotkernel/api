<?php

declare(strict_types=1);

namespace Api\User;

use Api\User\Entity\UserResetPasswordEntity;
use Api\User\Handler\AccountActivateHandler;
use Api\User\Handler\AccountAvatarHandler;
use Api\User\Handler\AccountHandler;
use Api\User\Handler\AccountResetPasswordHandler;
use Api\User\Handler\AccountSubscriptionHandler;
use Api\User\Handler\UserActivateHandler;
use Api\User\Handler\UserAvatarHandler;
use Api\User\Handler\UserHandler;
use Api\User\Handler\UserSubscriptionHandler;
use Api\User\Middleware\AuthMiddleware;
use Api\User\Collection\UserCollection;
use Api\User\Entity\UserEntity;
use Api\User\Service\UserRoleService;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Factory\AnnotatedServiceFactory;
use Zend\Expressive\Hal\Metadata\MetadataMap;
use Zend\Expressive\Hal\Metadata\RouteBasedCollectionMetadata;
use Zend\Expressive\Hal\Metadata\RouteBasedResourceMetadata;
use Zend\Hydrator\ArraySerializable;

/**
 * Class ConfigProvider
 * @package Api\User
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig(),
            'templates' => $this->getTemplates()
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'factories'  => [
                AccountActivateHandler::class => AnnotatedServiceFactory::class,
                AccountAvatarHandler::class => AnnotatedServiceFactory::class,
                AccountHandler::class => AnnotatedServiceFactory::class,
                AccountResetPasswordHandler::class => AnnotatedServiceFactory::class,
                AccountSubscriptionHandler::class => AnnotatedServiceFactory::class,
                AuthMiddleware::class => AnnotatedServiceFactory::class,
                UserActivateHandler::class => AnnotatedServiceFactory::class,
                UserAvatarHandler::class => AnnotatedServiceFactory::class,
                UserHandler::class => AnnotatedServiceFactory::class,
                UserService::class => AnnotatedServiceFactory::class,
                UserRoleService::class => AnnotatedServiceFactory::class,
                UserSubscriptionHandler::class => AnnotatedServiceFactory::class,
            ]
        ];
    }

    /**
     * @return array
     */
    public function getHalConfig() : array
    {
        return [
            [
                '__class__' => RouteBasedCollectionMetadata::class,
                'collection_class' => UserCollection::class,
                'collection_relation' => 'users',
                'route' => 'user:list,create',
            ], [
                '__class__' => RouteBasedResourceMetadata::class,
                'resource_class' => UserEntity::class,
                'route' => 'user:delete,view,update',
                'extractor' => ArraySerializable::class,
                'resource_identifier' => 'uuid',
                'route_identifier_placeholder' => 'uuid'
            ], [
                '__class__' => RouteBasedResourceMetadata::class,
                'resource_class' => UserResetPasswordEntity::class,
                'route' => 'account:reset-password',
                'extractor' => ArraySerializable::class,
                'resource_identifier' => 'uuid',
                'route_identifier_placeholder' => 'uuid'
            ]
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
