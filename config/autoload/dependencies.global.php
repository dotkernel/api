<?php

declare(strict_types=1);

use Api\App\Factory\ErrorResponseGeneratorFactory;
use Api\App\Factory\UserIdentityFactory;
use Core\Security\Repository\OAuthAccessTokenRepository;
use Core\Security\Repository\OAuthAuthCodeRepository;
use Core\Security\Repository\OAuthClientRepository;
use Core\Security\Repository\OAuthRefreshTokenRepository;
use Core\Security\Repository\OAuthScopeRepository;
use Core\User\Repository\UserRepository;
use Core\User\UserIdentity;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Authorization\Rbac\LaminasRbac;
use Mezzio\Middleware\ErrorResponseGenerator;
use Roave\PsrContainerDoctrine\Migrations\CommandFactory;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as service names.
    'dependencies' => [
        // Use 'aliases' to alias a service name to another service.
        // The key is the alias name, the value is the service to which it points.
        'aliases' => [
            AccessTokenRepositoryInterface::class  => OAuthAccessTokenRepository::class,
            AuthCodeRepositoryInterface::class     => OAuthAuthCodeRepository::class,
            ClientRepositoryInterface::class       => OAuthClientRepository::class,
            UserInterface::class                   => UserIdentity::class,
            AuthorizationInterface::class          => LaminasRbac::class,
            RefreshTokenRepositoryInterface::class => OAuthRefreshTokenRepository::class,
            ScopeRepositoryInterface::class        => OAuthScopeRepository::class,
            UserRepositoryInterface::class         => UserRepository::class,
        ],
        // Use 'invokables' for constructor-less services, or services that do not require arguments to the constructor.
        // Map a service name to the class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            ExecuteCommand::class         => CommandFactory::class,
            ErrorResponseGenerator::class => ErrorResponseGeneratorFactory::class,
            UserIdentity::class           => UserIdentityFactory::class,
        ],
    ],
];
