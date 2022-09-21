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
use Api\Admin\Handler\AdminHandler;
use Api\Admin\Handler\AdminRoleHandler;
use Api\Admin\Repository\AdminRepository;
use Api\Admin\Repository\AdminRoleRepository;
use Api\Admin\Service\AdminRoleService;
use Api\Admin\Service\AdminService;
use Api\App\ConfigProvider as AppConfigProvider;
use Dot\AnnotatedServices\Factory\AnnotatedRepositoryFactory;
use Dot\AnnotatedServices\Factory\AnnotatedServiceFactory;
use Mezzio\Hal\Metadata\MetadataMap;

/**
 * Class ConfigProvider
 * @package Api\Admin
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig(),
        ];
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                AdminHandler::class => AnnotatedServiceFactory::class,
                AdminAccountHandler::class => AnnotatedServiceFactory::class,
                AdminRoleHandler::class => AnnotatedServiceFactory::class,
                AdminService::class => AnnotatedServiceFactory::class,
                AdminRoleService::class => AnnotatedServiceFactory::class,
                AdminCreateCommand::class => AdminCreateCommandFactory::class,
                AdminRepository::class => AnnotatedRepositoryFactory::class,
                AdminRoleRepository::class => AnnotatedRepositoryFactory::class,
            ]
        ];
    }

    /**
     * @return array
     */
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
