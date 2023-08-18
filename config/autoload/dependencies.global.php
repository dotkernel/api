<?php

declare(strict_types=1);

use Api\App\Factory\ErrorResponseGeneratorFactory;
use Api\App\Factory\OAuthAccessTokenRepositoryFactory;
use Api\App\Factory\OAuthAuthCodeRepositoryFactory;
use Api\App\Factory\OAuthClientRepositoryFactory;
use Api\App\Factory\OAuthRefreshTokenRepositoryFactory;
use Api\App\Factory\OAuthScopeRepositoryFactory;
use Api\App\Factory\UserIdentityFactory;
use Api\App\Factory\UserRepositoryFactory;
use Api\App\Repository\OAuthAccessTokenRepository;
use Api\App\Repository\OAuthAuthCodeRepository;
use Api\App\Repository\OAuthClientRepository;
use Api\App\Repository\OAuthRefreshTokenRepository;
use Api\App\Repository\OAuthScopeRepository;
use Api\App\UserIdentity;
use Api\User\Repository\UserRepository;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Dot\ErrorHandler\ErrorHandlerInterface;
use Dot\ErrorHandler\LogErrorHandler;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Roave\PsrContainerDoctrine\Migrations\CommandFactory;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service. The
        // key is the alias name, the value is the service to which it points.
        'aliases' => [
            AccessTokenRepositoryInterface::class              => OAuthAccessTokenRepository::class,
            AuthCodeRepositoryInterface::class                 => OAuthAuthCodeRepository::class,
            ClientRepositoryInterface::class                   => OAuthClientRepository::class,
            RefreshTokenRepositoryInterface::class             => OAuthRefreshTokenRepository::class,
            ScopeRepositoryInterface::class                    => OAuthScopeRepository::class,
            ErrorHandlerInterface::class                       => LogErrorHandler::class,
            Mezzio\Authentication\UserInterface::class         => UserIdentity::class,
            Mezzio\Authorization\AuthorizationInterface::class => Mezzio\Authorization\Rbac\LaminasRbac::class,
            UserRepositoryInterface::class                     => UserRepository::class,
        ],
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            ExecuteCommand::class                           => CommandFactory::class,
            Mezzio\Middleware\ErrorResponseGenerator::class => ErrorResponseGeneratorFactory::class,
            OAuthAccessTokenRepository::class               => OAuthAccessTokenRepositoryFactory::class,
            OAuthAuthCodeRepository::class                  => OAuthAuthCodeRepositoryFactory::class,
            OAuthClientRepository::class                    => OAuthClientRepositoryFactory::class,
            OAuthRefreshTokenRepository::class              => OAuthRefreshTokenRepositoryFactory::class,
            OAuthScopeRepository::class                     => OAuthScopeRepositoryFactory::class,
            UserRepository::class                           => UserRepositoryFactory::class,
            UserIdentity::class                             => UserIdentityFactory::class,
        ],
    ],
];
