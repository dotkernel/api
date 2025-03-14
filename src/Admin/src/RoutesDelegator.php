<?php

declare(strict_types=1);

namespace Api\Admin;

use Api\Admin\Handler\Account\GetAdminAccountResourceHandler;
use Api\Admin\Handler\Account\PatchAdminAccountResourceHandler;
use Api\Admin\Handler\Admin\DeleteAdminResourceHandler;
use Api\Admin\Handler\Admin\GetAdminCollectionHandler;
use Api\Admin\Handler\Admin\GetAdminResourceHandler;
use Api\Admin\Handler\Admin\PatchAdminResourceHandler;
use Api\Admin\Handler\Admin\PostAdminResourceHandler;
use Api\Admin\Handler\Admin\Role\GetAdminRoleCollectionHandler;
use Api\Admin\Handler\Admin\Role\GetAdminRoleResourceHandler;
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

        $app->get('/admin', GetAdminCollectionHandler::class, 'admin::list-admin');
        $app->post('/admin', PostAdminResourceHandler::class, 'admin::create-admin');

        $app->delete('/admin/' . $uuid, DeleteAdminResourceHandler::class, 'admin::delete-admin');
        $app->get('/admin/' . $uuid, GetAdminResourceHandler::class, 'admin::view-admin');
        $app->patch('/admin/' . $uuid, PatchAdminResourceHandler::class, 'admin::update-admin');

        $app->get('/admin/role', GetAdminRoleCollectionHandler::class, 'admin::list-role');
        $app->get('/admin/role/' . $uuid, GetAdminRoleResourceHandler::class, 'admin::view-role');

        $app->get('/admin/account', GetAdminAccountResourceHandler::class, 'admin::view-account');
        $app->patch('/admin/account', PatchAdminAccountResourceHandler::class, 'admin::update-account');

        return $app;
    }
}
