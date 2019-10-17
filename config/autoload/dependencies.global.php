<?php

declare(strict_types=1);

use Api\App\Common\Factory\AccessTokenRepositoryFactory;
use Api\App\Common\Repository\AccessTokenRepository;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Zend\Expressive as Expressive;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
            AccessTokenRepositoryInterface::class => AccessTokenRepository::class,
            Dot\ErrorHandler\ErrorHandlerInterface::class => Dot\ErrorHandler\LogErrorHandler::class,
            Expressive\Authentication\UserInterface::class => Api\User\Entity\UserIdentity::class,
            Expressive\Authorization\AuthorizationInterface::class => Expressive\Authorization\Rbac\ZendRbac::class,
            League\OAuth2\Server\Repositories\UserRepositoryInterface::class =>
                Api\App\Common\Repository\OauthUserRepository::class,
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories'  => [
            AccessTokenRepository::class => AccessTokenRepositoryFactory::class,
            Api\App\Common\Repository\OauthUserRepository::class =>
                Api\App\Common\Factory\OauthUserRepositoryFactory::class,
            Api\User\Entity\UserIdentity::class => Api\User\Factory\UserIdentityFactory::class,
            Tuupola\Middleware\CorsMiddleware::class => Api\App\Cors\Factory\CorsFactory::class
        ],
    ],
];
