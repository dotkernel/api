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
        $uuid = \Api\App\RoutesDelegator::REGEXP_UUID;

        /** @var RouteCollectorInterface $routeCollector */
        $routeCollector = $container->get(RouteCollectorInterface::class);

        $routeCollector->group('/admin')
            ->get('', GetAdminCollectionHandler::class, 'admin::list-admin')
            ->post('', PostAdminResourceHandler::class, 'admin::create-admin');

        $routeCollector->group('/admin/' . $uuid)
            ->delete('', DeleteAdminResourceHandler::class, 'admin::delete-admin')
            ->get('', GetAdminResourceHandler::class, 'admin::view-admin')
            ->patch('', PatchAdminResourceHandler::class, 'admin::update-admin');

        $routeCollector->group('/admin/role')
            ->get('', GetAdminRoleCollectionHandler::class, 'admin::list-role')
            ->get('/' . $uuid, GetAdminRoleResourceHandler::class, 'admin::view-role');

        $routeCollector->group('/admin/account')
            ->get('', GetAdminAccountResourceHandler::class, 'admin::view-account')
            ->patch('', PatchAdminAccountResourceHandler::class, 'admin::update-account');

        return $callback();
    }
}
