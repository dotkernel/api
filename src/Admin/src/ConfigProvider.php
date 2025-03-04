<?php

declare(strict_types=1);

namespace Api\Admin;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Command\AdminCreateCommand;
use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Factory\AdminCreateCommandFactory;
use Api\Admin\Handler\AdminAccountHandler;
use Api\Admin\Handler\AdminCollectionHandler;
use Api\Admin\Handler\AdminHandler;
use Api\Admin\Handler\AdminRoleCollectionHandler;
use Api\Admin\Handler\AdminRoleHandler;
use Api\Admin\Repository\AdminRepository;
use Api\Admin\Repository\AdminRoleRepository;
use Api\Admin\Service\AdminRoleService;
use Api\Admin\Service\AdminRoleServiceInterface;
use Api\Admin\Service\AdminService;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\ConfigProvider as AppConfigProvider;
use Api\App\Factory\HandlerDelegatorFactory;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Dot\DependencyInjection\Factory\AttributedRepositoryFactory;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Mezzio\Application;
use Mezzio\Hal\Metadata\MetadataMap;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'     => $this->getDependencies(),
            'doctrine'         => $this->getDoctrineConfig(),
            MetadataMap::class => $this->getHalConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class                => [RoutesDelegator::class],
                AdminAccountHandler::class        => [HandlerDelegatorFactory::class],
                AdminCollectionHandler::class     => [HandlerDelegatorFactory::class],
                AdminHandler::class               => [HandlerDelegatorFactory::class],
                AdminRoleCollectionHandler::class => [HandlerDelegatorFactory::class],
                AdminRoleHandler::class           => [HandlerDelegatorFactory::class],
            ],
            'factories'  => [
                AdminAccountHandler::class        => AttributedServiceFactory::class,
                AdminCollectionHandler::class     => AttributedServiceFactory::class,
                AdminCreateCommand::class         => AdminCreateCommandFactory::class,
                AdminHandler::class               => AttributedServiceFactory::class,
                AdminRepository::class            => AttributedRepositoryFactory::class,
                AdminRoleCollectionHandler::class => AttributedServiceFactory::class,
                AdminRoleHandler::class           => AttributedServiceFactory::class,
                AdminRoleRepository::class        => AttributedRepositoryFactory::class,
                AdminRoleService::class           => AttributedServiceFactory::class,
                AdminService::class               => AttributedServiceFactory::class,
            ],
            'aliases'    => [
                AdminRoleServiceInterface::class => AdminRoleService::class,
                AdminServiceInterface::class     => AdminService::class,
            ],
        ];
    }

    private function getDoctrineConfig(): array
    {
        return [
            'driver' => [
                'orm_default'   => [
                    'drivers' => [
                        'Api\Admin\Entity' => 'AdminEntities',
                    ],
                ],
                'AdminEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => __DIR__ . '/Entity',
                ],
            ],
        ];
    }

    public function getHalConfig(): array
    {
        return [
            AppConfigProvider::getCollection(AdminCollection::class, 'admin.list', 'admins'),
            AppConfigProvider::getCollection(AdminRoleCollection::class, 'admin.role.list', 'roles'),
            AppConfigProvider::getResource(Admin::class, 'admin.view'),
            AppConfigProvider::getResource(AdminRole::class, 'admin.role.view'),
        ];
    }
}
