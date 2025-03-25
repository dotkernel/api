<?php

declare(strict_types=1);

namespace Api\Admin;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Command\AdminCreateCommand;
use Api\Admin\Handler\Account\GetAdminAccountResourceHandler;
use Api\Admin\Handler\Account\PatchAdminAccountResourceHandler;
use Api\Admin\Handler\Admin\DeleteAdminResourceHandler;
use Api\Admin\Handler\Admin\GetAdminCollectionHandler;
use Api\Admin\Handler\Admin\GetAdminResourceHandler;
use Api\Admin\Handler\Admin\PatchAdminResourceHandler;
use Api\Admin\Handler\Admin\PostAdminResourceHandler;
use Api\Admin\Handler\Admin\Role\GetAdminRoleCollectionHandler;
use Api\Admin\Handler\Admin\Role\GetAdminRoleResourceHandler;
use Api\App\ConfigProvider as AppConfigProvider;
use Api\App\Factory\HandlerDelegatorFactory;
use Core\Admin\Entity\Admin;
use Core\Admin\Entity\AdminRole;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'     => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class                      => [RoutesDelegator::class],
                DeleteAdminResourceHandler::class       => [HandlerDelegatorFactory::class],
                GetAdminAccountResourceHandler::class   => [HandlerDelegatorFactory::class],
                GetAdminCollectionHandler::class        => [HandlerDelegatorFactory::class],
                GetAdminResourceHandler::class          => [HandlerDelegatorFactory::class],
                GetAdminRoleCollectionHandler::class    => [HandlerDelegatorFactory::class],
                GetAdminRoleResourceHandler::class      => [HandlerDelegatorFactory::class],
                PatchAdminAccountResourceHandler::class => [HandlerDelegatorFactory::class],
                PatchAdminResourceHandler::class        => [HandlerDelegatorFactory::class],
                PostAdminResourceHandler::class         => [HandlerDelegatorFactory::class],
            ],
            'factories'  => [
                AdminCreateCommand::class               => AttributedServiceFactory::class,
                DeleteAdminResourceHandler::class       => AttributedServiceFactory::class,
                GetAdminAccountResourceHandler::class   => AttributedServiceFactory::class,
                GetAdminCollectionHandler::class        => AttributedServiceFactory::class,
                GetAdminResourceHandler::class          => AttributedServiceFactory::class,
                GetAdminRoleCollectionHandler::class    => AttributedServiceFactory::class,
                GetAdminRoleResourceHandler::class      => AttributedServiceFactory::class,
                PatchAdminAccountResourceHandler::class => AttributedServiceFactory::class,
                PatchAdminResourceHandler::class        => AttributedServiceFactory::class,
                PostAdminResourceHandler::class         => AttributedServiceFactory::class,
            ],
        ];
    }

    private function getHalConfig(): array
    {
        return [
            AppConfigProvider::getCollection(AdminCollection::class, 'admin::list-admin', 'admins'),
            AppConfigProvider::getCollection(AdminRoleCollection::class, 'admin::list-role', 'roles'),
            AppConfigProvider::getResource(Admin::class, 'admin::view-admin'),
            AppConfigProvider::getResource(AdminRole::class, 'admin::view-role'),
        ];
    }
}
