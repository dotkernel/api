<?php

declare(strict_types=1);

namespace App;

use App\Auth\Middleware\AuthMiddleware;
use App\Auth\Factory\AuthMiddlewareFactory;
use App\User\Collection\UserCollection;
use App\User\Entity\UserAvatarEntity;
use App\User\Entity\UserEntity;
use App\User\Factory\UserServiceFactory;
use App\User\Form\UserAvatarInputFilter;
use App\User\Form\UserCreateInputFilter;
use App\User\Form\UserUpdateInputFilter;
use App\User\Service\UserService;
use ContainerInteropDoctrine\EntityManagerFactory;
use Zend\Expressive\Application;
use Zend\Expressive\Authentication;
use Zend\Expressive\Hal\Metadata\MetadataMap;
use Zend\Expressive\Hal\Metadata\RouteBasedCollectionMetadata;
use Zend\Expressive\Hal\Metadata\RouteBasedResourceMetadata;
use Zend\Hydrator\ArraySerializable;
use Zend\Hydrator\ObjectProperty as ObjectPropertyHydrator;
use Zend\Hydrator\Reflection;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
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
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'delegators' => [
                Application::class => [
                    RoutesDelegator::class
                ]
            ],
            'factories'  => [
                'doctrine.entity_manager.orm_default' => EntityManagerFactory::class,
                UserService::class => UserServiceFactory::class,
                AuthMiddleware::class => AuthMiddlewareFactory::class,
            ],
            'aliases' => [
                Authentication\AuthenticationInterface::class => Authentication\OAuth2\OAuth2Adapter::class,
            ],
            'invokables' => [
                'UserAvatarInputFilter' => UserAvatarInputFilter::class,
                'UserCreateInputFilter' => UserCreateInputFilter::class,
                'UserUpdateInputFilter' => UserUpdateInputFilter::class,
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
                'route' => 'users',
            ], [
                '__class__' => RouteBasedResourceMetadata::class,
                'resource_class' => UserEntity::class,
                'route' => 'user',
                'extractor' => ArraySerializable::class,
                'resource_identifier' => 'uuid',
                'route_identifier_placeholder' => 'uuid'
            ]
        ];
    }
}
