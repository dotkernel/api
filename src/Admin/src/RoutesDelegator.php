<?php

declare(strict_types=1);

namespace Api\Admin;

use Api\Admin\Handler\AdminAccountHandler;
use Api\Admin\Handler\AdminHandler;
use Api\Admin\Handler\AdminRoleHandler;
use Psr\Container\ContainerInterface;
use Mezzio\Application;

/**
 * Class RoutesDelegator
 * @package Api\Admin
 */
class RoutesDelegator
{
    /**
     * @param ContainerInterface $container
     * @param $serviceName
     * @param callable $callback
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        $uuid = \Api\App\RoutesDelegator::REGEXP_UUID;

        $app->get('/admin/my-account', AdminAccountHandler::class, 'admin.my-account.view');
        $app->patch('/admin/my-account', AdminAccountHandler::class, 'admin.my-account.update');

        $app->post('/admin', AdminHandler::class, 'admin.create');
        $app->delete('/admin/' . $uuid, AdminHandler::class, 'admin.delete');
        $app->get('/admin', AdminHandler::class, 'admin.list');
        $app->patch('/admin/' . $uuid, AdminHandler::class, 'admin.update');
        $app->get('/admin/' . $uuid, AdminHandler::class, 'admin.view');

        $app->get('/admin/role', AdminRoleHandler::class, 'admin.role.list');
        $app->get('/admin/role/' . $uuid, AdminRoleHandler::class, 'admin.role.view');

        return $app;
    }
}
