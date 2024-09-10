<?php

declare(strict_types=1);

namespace Api\User;

use Api\User\Handler\AccountActivateHandler;
use Api\User\Handler\AccountAvatarHandler;
use Api\User\Handler\AccountHandler;
use Api\User\Handler\AccountRecoveryHandler;
use Api\User\Handler\AccountResetPasswordHandler;
use Api\User\Handler\UserActivateHandler;
use Api\User\Handler\UserAvatarHandler;
use Api\User\Handler\UserCollectionHandler;
use Api\User\Handler\UserHandler;
use Api\User\Handler\UserRoleCollectionHandler;
use Api\User\Handler\UserRoleHandler;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

use function assert;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $app = $callback();
        assert($app instanceof Application);

        $uuid = \Api\App\RoutesDelegator::REGEXP_UUID;

        /**
         * Admins manage user accounts
         */

        $app->post(
            '/user',
            UserHandler::class,
            'user.create'
        );
        $app->delete(
            '/user/' . $uuid,
            UserHandler::class,
            'user.delete'
        );
        $app->get(
            '/user',
            UserCollectionHandler::class,
            'user.list'
        );
        $app->patch(
            '/user/' . $uuid,
            UserHandler::class,
            'user.update'
        );
        $app->get(
            '/user/' . $uuid,
            UserHandler::class,
            'user.view'
        );

        $app->patch(
            '/user/' . $uuid . '/activate',
            UserActivateHandler::class,
            'user.activate'
        );

        $app->delete(
            '/user/' . $uuid . '/avatar',
            UserAvatarHandler::class,
            'user.avatar.delete'
        );
        $app->get(
            '/user/' . $uuid . '/avatar',
            UserAvatarHandler::class,
            'user.avatar.view'
        );
        $app->post(
            '/user/' . $uuid . '/avatar',
            UserAvatarHandler::class,
            'user.avatar.create'
        );

        $app->get(
            '/user/role',
            UserRoleCollectionHandler::class,
            'user.role.list'
        );
        $app->get(
            '/user/role/' . $uuid,
            UserRoleHandler::class,
            'user.role.view'
        );

        /**
         * Users manage their own accounts
         */

        $app->delete(
            '/user/my-account',
            AccountHandler::class,
            'user.my-account.delete'
        );
        $app->get(
            '/user/my-account',
            AccountHandler::class,
            'user.my-account.view'
        );
        $app->patch(
            '/user/my-account',
            AccountHandler::class,
            'user.my-account.update'
        );

        $app->post(
            '/user/my-avatar',
            AccountAvatarHandler::class,
            'user.my-avatar.create'
        );
        $app->delete(
            '/user/my-avatar',
            AccountAvatarHandler::class,
            'user.my-avatar.delete'
        );
        $app->get(
            '/user/my-avatar',
            AccountAvatarHandler::class,
            'user.my-avatar.view'
        );

        /**
         * Unauthenticated users manage their accounts
         */

        $app->post(
            '/account/register',
            AccountHandler::class,
            'account.register'
        );

        $app->get(
            '/account/reset-password/{hash}',
            AccountResetPasswordHandler::class,
            'account.reset-password.validate'
        );
        $app->patch(
            '/account/reset-password/{hash}',
            AccountResetPasswordHandler::class,
            'account.modify-password'
        );
        $app->post(
            '/account/reset-password',
            AccountResetPasswordHandler::class,
            'account.reset-password.request'
        );

        $app->post(
            '/account/recover-identity',
            AccountRecoveryHandler::class,
            'account.recover-identity'
        );

        $app->patch(
            '/account/activate/{hash}',
            AccountActivateHandler::class,
            'account.activate'
        );
        $app->post(
            '/account/activate',
            AccountActivateHandler::class,
            'account.activate.request'
        );

        return $app;
    }
}
