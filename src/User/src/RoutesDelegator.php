<?php

declare(strict_types=1);

namespace Api\User;

use Api\User\Handler\AccountActivateHandler;
use Api\User\Handler\AccountAvatarHandler;
use Api\User\Handler\AccountHandler;
use Api\User\Handler\AccountRecoveryHandler;
use Api\User\Handler\AccountResetPasswordHandler;
use Api\User\Handler\AdminAccountHandler;
use Api\User\Handler\UserActivateHandler;
use Api\User\Handler\UserAvatarHandler;
use Api\User\Handler\UserHandler;
use Api\User\Middleware\AuthMiddleware;
use Fig\Http\Message\RequestMethodInterface as RequestMethod;
use Psr\Container\ContainerInterface;
use Mezzio\Application;
use Mezzio\Authentication\AuthenticationMiddleware;

/**
 * Class RoutesDelegator
 * @package Api\User
 */
class RoutesDelegator
{
    /**
     * @param ContainerInterface $container
     * @param $serviceName
     * @param callable $callback
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback)
    {
        /** @var Application $app */
        $app = $callback();

        /**
         * Admins manage users' accounts
         */

        $app->route(
            '/user',
            [AuthenticationMiddleware::class, AuthMiddleware::class, UserHandler::class],
            [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST],
            'user:list,create'
        );

        $app->route(
            '/user/' . \Api\App\RoutesDelegator::REGEXP_UUID,
            [AuthenticationMiddleware::class, AuthMiddleware::class, UserHandler::class],
            [RequestMethod::METHOD_DELETE, RequestMethod::METHOD_GET, RequestMethod::METHOD_PATCH],
            'user:delete,view,update'
        );

        $app->post(
            '/user/activate/' . \Api\App\RoutesDelegator::REGEXP_UUID,
            [AuthenticationMiddleware::class, AuthMiddleware::class, UserActivateHandler::class],
            'user:activate'
        );

        $app->post(
            '/user/avatar/' . \Api\App\RoutesDelegator::REGEXP_UUID,
            [AuthenticationMiddleware::class, AuthMiddleware::class, UserAvatarHandler::class],
            'user:avatar'
        );

        /**
         * Users manage their own accounts
         */

        $app->route(
            '/my-account',
            [AuthenticationMiddleware::class, AuthMiddleware::class, AccountHandler::class],
            [RequestMethod::METHOD_DELETE, RequestMethod::METHOD_GET, RequestMethod::METHOD_PATCH],
            'my-account:me'
        );

        $app->post(
            '/my-account/avatar',
            [AuthenticationMiddleware::class, AuthMiddleware::class, AccountAvatarHandler::class],
            'my-account:avatar'
        );

        $app->post('/account/register', AccountHandler::class, 'account:register');

        $app->route(
            '/account/reset-password[/{hash}]',
            AccountResetPasswordHandler::class,
            [RequestMethod::METHOD_GET, RequestMethod::METHOD_PATCH, RequestMethod::METHOD_POST],
            'account:reset-password'
        );

        $app->post(
            '/account/recover-identity',
            AccountRecoveryHandler::class,
            'account:recover-identity'
        );

        $app->route(
            '/account/activate[/{hash}]',
            AccountActivateHandler::class,
            [RequestMethod::METHOD_GET, RequestMethod::METHOD_POST],
            'account:activate'
        );

        /**
         * Admins manage their own accounts
         */

        $app->route(
            '/admin/my-account',
            [AuthenticationMiddleware::class, AuthMiddleware::class, AdminAccountHandler::class],
            [RequestMethod::METHOD_GET, RequestMethod::METHOD_PATCH],
            'admin-my-account:me'
        );

        $app->post('/admin', AdminAccountHandler::class, 'admin:register');

        return $app;
    }
}
