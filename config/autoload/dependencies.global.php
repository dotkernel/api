<?php

declare(strict_types=1);

use Zend\Expressive as Expressive;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
            Dot\ErrorHandler\ErrorHandlerInterface::class => Dot\ErrorHandler\LogErrorHandler::class,
            League\OAuth2\Server\Repositories\UserRepositoryInterface::class => App\Common\OauthUserRepository::class,
            Expressive\Authentication\UserInterface::class => App\User\Entity\UserIdentity::class,
            Expressive\Authorization\AuthorizationInterface::class => Expressive\Authorization\Rbac\ZendRbac::class
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories'  => [
            App\Common\OauthUserRepository::class => App\Common\OauthUserRepositoryFactory::class,
            App\User\Entity\UserIdentity::class => App\User\Factory\UserIdentityFactory::class,
            Tuupola\Middleware\CorsMiddleware::class => App\Cors\Factory\CorsFactory::class
        ],
    ],
];
