<?php

declare(strict_types=1);

namespace Api\User;

use Api\User\Handler\Account\Avatar\DeleteUserAccountAvatarResourceHandler;
use Api\User\Handler\Account\Avatar\GetUserAccountAvatarResourceHandler;
use Api\User\Handler\Account\Avatar\PostUserAccountAvatarResourceHandler;
use Api\User\Handler\Account\DeleteUserAccountResourceHandler;
use Api\User\Handler\Account\GetUserAccountResourceHandler;
use Api\User\Handler\Account\PatchUserAccountActivateHandler;
use Api\User\Handler\Account\PatchUserAccountResourceHandler;
use Api\User\Handler\Account\PostUserAccountActivateHandler;
use Api\User\Handler\Account\PostUserAccountRecoverHandler;
use Api\User\Handler\Account\PostUserAccountResourceHandler;
use Api\User\Handler\Account\ResetPassword\GetUserAccountResetPasswordResourceHandler;
use Api\User\Handler\Account\ResetPassword\PatchUserAccountResetPasswordResourceHandler;
use Api\User\Handler\Account\ResetPassword\PostUserAccountResetPasswordResourceHandler;
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
use Core\App\ConfigProvider;
use Dot\Router\RouteCollectorInterface;
use Mezzio\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class RoutesDelegator
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $uuid = ConfigProvider::REGEXP_UUID;

        /** @var RouteCollectorInterface $routeCollector */
        $routeCollector = $container->get(RouteCollectorInterface::class);

        $routeCollector->group('/user')
            ->get('', GetUserCollectionHandler::class, 'user::list-user')
            ->post('', PostUserResourceHandler::class, 'user::create-user');

        $routeCollector
            ->patch('/user/' . $uuid . '/activate', PatchUserActivateHandler::class, 'user::activate-user')
            ->patch('/user/' . $uuid . '/deactivate', PatchUserDeactivateHandler::class, 'user::deactivate-user');

        $routeCollector->group('/user/' . $uuid)
            ->delete('', DeleteUserResourceHandler::class, 'user::delete-user')
            ->get('', GetUserResourceHandler::class, 'user::view-user')
            ->patch('', PatchUserResourceHandler::class, 'user::update-user');

        $routeCollector->group('/user/' . $uuid . '/avatar')
            ->delete('', DeleteUserAvatarResourceHandler::class, 'user::delete-user-avatar')
            ->get('', GetUserAvatarResourceHandler::class, 'user::view-user-avatar')
            ->post('', PostUserAvatarResourceHandler::class, 'user::create-user-avatar');

        $routeCollector->group('/user/role')
            ->get('', GetUserRoleCollectionHandler::class, 'user::list-role')
            ->get('/' . $uuid, GetUserRoleResourceHandler::class, 'user::view-role');

        $routeCollector->group('/user/account')
            ->delete('', DeleteUserAccountResourceHandler::class, 'user::delete-account')
            ->get('', GetUserAccountResourceHandler::class, 'user::view-account')
            ->patch('', PatchUserAccountResourceHandler::class, 'user::update-account')
            ->post('', PostUserAccountResourceHandler::class, 'user::create-account');

        $routeCollector->group('/user/account/activate')
            ->patch('/{hash}', PatchUserAccountActivateHandler::class, 'user::activate-account')
            ->post('', PostUserAccountActivateHandler::class, 'user::request-activate-account');

        $routeCollector->post('/user/account/recover', PostUserAccountRecoverHandler::class, 'user::recover-account');

        $routeCollector->group('/user/account/avatar')
            ->delete('', DeleteUserAccountAvatarResourceHandler::class, 'user::delete-account-avatar')
            ->get('', GetUserAccountAvatarResourceHandler::class, 'user::view-account-avatar')
            ->post('', PostUserAccountAvatarResourceHandler::class, 'user::create-account-avatar');

        $routeCollector
            ->get(
                '/user/account/reset-password/{hash}',
                GetUserAccountResetPasswordResourceHandler::class,
                'user::check-account-reset-password'
            )
            ->patch(
                '/user/account/reset-password/{hash}',
                PatchUserAccountResetPasswordResourceHandler::class,
                'user::update-account-reset-password'
            )
            ->post(
                '/user/account/reset-password',
                PostUserAccountResetPasswordResourceHandler::class,
                'user::create-account-reset-password'
            );

        return $callback();
    }
}
