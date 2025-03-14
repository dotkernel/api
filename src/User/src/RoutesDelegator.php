<?php

declare(strict_types=1);

namespace Api\User;

use Api\User\Handler\Account\Avatar\DeleteUserAccountAvatarHandler;
use Api\User\Handler\Account\Avatar\GetUserAccountAvatarHandler;
use Api\User\Handler\Account\Avatar\PostUserAccountAvatarHandler;
use Api\User\Handler\Account\DeleteUserAccountResourceHandler;
use Api\User\Handler\Account\GetUserAccountResourceHandler;
use Api\User\Handler\Account\PatchUserAccountActivateHandler;
use Api\User\Handler\Account\PatchUserAccountResourceHandler;
use Api\User\Handler\Account\PostUserAccountActivateHandler;
use Api\User\Handler\Account\PostUserAccountRecoverHandler;
use Api\User\Handler\Account\PostUserAccountResourceHandler;
use Api\User\Handler\Account\ResetPassword\GetUserAccountResetPasswordHandler;
use Api\User\Handler\Account\ResetPassword\PatchUserAccountResetPasswordHandler;
use Api\User\Handler\Account\ResetPassword\PostUserAccountResetPasswordHandler;
use Api\User\Handler\User\Avatar\DeleteUserAvatarResourceHandler;
use Api\User\Handler\User\Avatar\GetUserAvatarResourceHandler;
use Api\User\Handler\User\Avatar\PostUserAvatarResourceHandler;
use Api\User\Handler\User\DeleteUserResourceHandler;
use Api\User\Handler\User\GetUserCollectionHandler;
use Api\User\Handler\User\GetUserResourceHandler;
use Api\User\Handler\User\PatchUserActivateHandler;
use Api\User\Handler\User\PatchUserDeactivateHandler;
use Api\User\Handler\User\PatchUserResourceHandler;
use Api\User\Handler\User\PostUserResourceHandler;
use Api\User\Handler\User\Role\GetUserRoleCollectionHandler;
use Api\User\Handler\User\Role\GetUserRoleResourceHandler;
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

        // Accounts having higher than user permissions manage user accounts

        $app->get('/user', GetUserCollectionHandler::class, 'user::list-user');
        $app->post('/user', PostUserResourceHandler::class, 'user::create-user');

        $app->delete('/user/' . $uuid, DeleteUserResourceHandler::class, 'user::delete-user');
        $app->get('/user/' . $uuid, GetUserResourceHandler::class, 'user::view-user');
        $app->patch('/user/' . $uuid, PatchUserResourceHandler::class, 'user::update-user');

        $app->delete('/user/' . $uuid . '/avatar', DeleteUserAvatarResourceHandler::class, 'user::delete-user-avatar');
        $app->get('/user/' . $uuid . '/avatar', GetUserAvatarResourceHandler::class, 'user::view-user-avatar');
        $app->post('/user/' . $uuid . '/avatar', PostUserAvatarResourceHandler::class, 'user::create-user-avatar');

        $app->get('/user/role', GetUserRoleCollectionHandler::class, 'user::list-role');
        $app->get('/user/role/' . $uuid, GetUserRoleResourceHandler::class, 'user::view-role');

        $app->patch('/user/' . $uuid . '/activate', PatchUserActivateHandler::class, 'user::activate-user');
        $app->patch('/user/' . $uuid . '/deactivate', PatchUserDeactivateHandler::class, 'user::deactivate-user');

        // Users manage their user accounts

        $app->delete('/user/account', DeleteUserAccountResourceHandler::class, 'user::delete-account');
        $app->get('/user/account', GetUserAccountResourceHandler::class, 'user::view-account');
        $app->patch('/user/account', PatchUserAccountResourceHandler::class, 'user::update-account');
        $app->post('/user/account', PostUserAccountResourceHandler::class, 'user::create-account');

        $app->delete('/user/account/avatar', DeleteUserAccountAvatarHandler::class, 'user::delete-account-avatar');
        $app->get('/user/account/avatar', GetUserAccountAvatarHandler::class, 'user::view-account-avatar');
        $app->post('/user/account/avatar', PostUserAccountAvatarHandler::class, 'user::create-account-avatar');

        // Unauthenticated users manage their user accounts

        $app->patch('/user/account/activate/{hash}', PatchUserAccountActivateHandler::class, 'user::activate-account');
        $app->post('/user/account/activate', PostUserAccountActivateHandler::class, 'user::request-activate-account');

        $app->post('/user/account/recover', PostUserAccountRecoverHandler::class, 'user::recover-account');

        $app->get(
            '/user/account/reset-password/{hash}',
            GetUserAccountResetPasswordHandler::class,
            'user::check-account-reset-password'
        );
        $app->patch(
            '/user/account/reset-password/{hash}',
            PatchUserAccountResetPasswordHandler::class,
            'user::update-account-reset-password'
        );
        $app->post(
            '/user/account/reset-password',
            PostUserAccountResetPasswordHandler::class,
            'user::create-account-reset-password'
        );

        return $app;
    }
}
