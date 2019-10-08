<?php
/**
 * This file generated by Zend\Expressive\Tooling\Factory\ConfigInjector.
 *
 * Modifications should be kept at a minimum, and restricted to adding or
 * removing factory definitions; other dependency types may be overwritten
 * when regenerating this file via zend-expressive-tooling commands.
 */

declare(strict_types=1);

return [
    'dependencies' => [
        'factories' => [
            Api\User\Handler\AccountActivateHandler::class => Api\User\Factory\AccountActivateHandlerFactory::class,
            Api\User\Handler\AccountAvatarHandler::class => Api\User\Factory\AccountAvatarHandlerFactory::class,
            Api\User\Handler\AccountHandler::class => Api\User\Factory\AccountHandlerFactory::class,
            Api\User\Handler\UserActivateHandler::class => Api\User\Factory\UserActivateHandlerFactory::class,
            Api\User\Handler\UserAvatarHandler::class => Api\User\Factory\UserAvatarHandlerFactory::class,
            Api\User\Handler\UserHandler::class => Api\User\Factory\UserHandlerFactory::class,
        ],
    ],
];
