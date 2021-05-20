<?php

declare(strict_types=1);

use Api\App\Factory\AccessTokenRepositoryFactory;
use Api\App\Factory\ErrorResponseGeneratorFactory;
use Api\App\Factory\OauthUserRepositoryFactory;
use Api\App\Repository\AccessTokenRepository;
use Api\App\Repository\OauthUserRepository;
use Api\App\Factory\UserIdentityFactory;
use Api\App\UserIdentity;
use Dot\ErrorHandler\ErrorHandlerInterface;
use Dot\ErrorHandler\LogErrorHandler;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
            AccessTokenRepositoryInterface::class => AccessTokenRepository::class,
            ErrorHandlerInterface::class => LogErrorHandler::class,
            Mezzio\Authentication\UserInterface::class => UserIdentity::class,
            Mezzio\Authorization\AuthorizationInterface::class => Mezzio\Authorization\Rbac\LaminasRbac::class,
            UserRepositoryInterface::class => OauthUserRepository::class,
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories'  => [
            Mezzio\Middleware\ErrorResponseGenerator::class => ErrorResponseGeneratorFactory::class,
            AccessTokenRepository::class => AccessTokenRepositoryFactory::class,
            OauthUserRepository::class => OauthUserRepositoryFactory::class,
            UserIdentity::class => UserIdentityFactory::class,
        ],
    ],
];
