<?php

declare(strict_types=1);

namespace Core\App;

use Core\App\Command\RouteListCommand;
use Core\App\DBAL\Types\SuccessFailureEnumType;
use Core\App\DBAL\Types\YesNoEnumType;
use Core\App\Factory\EntityListenerResolverFactory;
use Core\App\Resolver\EntityListenerResolver;
use Core\App\Service\MailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\EntityListenerResolver as EntityListenerResolverInterface;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Dot\Cache\Adapter\ArrayAdapter;
use Dot\Cache\Adapter\FilesystemAdapter;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Dot\ErrorHandler\ErrorHandlerInterface;
use Dot\ErrorHandler\LogErrorHandler;
use Dot\Mail\Factory\MailOptionsAbstractFactory;
use Dot\Mail\Factory\MailServiceAbstractFactory;
use Dot\Mail\Service\MailService as DotMailService;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\Doctrine\UuidType;
use Roave\PsrContainerDoctrine\EntityManagerFactory;
use Symfony\Component\Cache\Adapter\AdapterInterface;

use function getcwd;

/**
 * @phpstan-type ConfigType array{
 *      dependencies: DependenciesType,
 *      doctrine: DoctrineConfigType,
 *      resultCacheLifetime: int,
 * }
 * @phpstan-type DoctrineConfigType array{
 *      cache: array{
 *          array: array{
 *              class: class-string<AdapterInterface>,
 *          },
 *          filesystem: array{
 *              class: class-string<AdapterInterface>,
 *              directory: non-empty-string,
 *              namespace: non-empty-string,
 *          },
 *      },
 *      configuration: array{
 *          orm_default: array{
 *              entity_listener_resolver: class-string<EntityListenerResolverInterface>,
 *              result_cache: non-empty-string,
 *              metadata_cache: non-empty-string,
 *              query_cache: non-empty-string,
 *              hydration_cache: non-empty-string,
 *              typed_field_mapper: non-empty-string|null,
 *              second_level_cache: array{
 *                  enabled: bool,
 *                  default_lifetime: int,
 *                  default_lock_lifetime: int,
 *                  file_lock_region_directory: string,
 *                  regions: non-empty-string[],
 *               },
 *          },
 *      },
 *      connection: array{
 *          orm_default: array{
 *              doctrine_mapping_types: array<non-empty-string, non-empty-string>,
 *          },
 *      },
 *      driver: array{
 *          orm_default: array{
 *              class: class-string<MappingDriver>,
 *          },
 *      },
 *      fixtures: non-empty-string,
 *      migrations: array{
 *          table_storage: array{
 *              table_name: non-empty-string,
 *              version_column_name: non-empty-string,
 *              version_column_length: int,
 *              executed_at_column_name: non-empty-string,
 *              execution_time_column_name: non-empty-string,
 *          },
 *          migrations_paths: array<non-empty-string, non-empty-string>,
 *          all_or_nothing: bool,
 *          check_database_platform: bool,
 *      },
 *      types: array<non-empty-string, class-string>,
 * }
 * @phpstan-type DependenciesType array{
 *       factories: array<class-string|non-empty-string, class-string|non-empty-string>,
 *       aliases: array<class-string|non-empty-string, class-string|non-empty-string>,
 * }
 */
class ConfigProvider
{
    public const REGEXP_UUID = '{uuid:[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}}';

    /**
     * @return ConfigType
     */
    public function __invoke(): array
    {
        return [
            'dependencies'        => $this->getDependencies(),
            'doctrine'            => $this->getDoctrineConfig(),
            'resultCacheLifetime' => 600,
        ];
    }

    /**
     * @return DependenciesType
     */
    private function getDependencies(): array
    {
        return [
            'factories' => [
                'doctrine.entity_manager.orm_default' => EntityManagerFactory::class,
                'dot-mail.options.default'            => MailOptionsAbstractFactory::class,
                'dot-mail.service.default'            => MailServiceAbstractFactory::class,
                EntityListenerResolver::class         => EntityListenerResolverFactory::class,
                MailService::class                    => AttributedServiceFactory::class,
                RouteListCommand::class               => AttributedServiceFactory::class,
            ],
            'aliases'   => [
                DotMailService::class         => 'dot-mail.service.default',
                EntityManager::class          => 'doctrine.entity_manager.orm_default',
                EntityManagerInterface::class => 'doctrine.entity_manager.orm_default',
                ErrorHandlerInterface::class  => LogErrorHandler::class,
            ],
        ];
    }

    /**
     * @return DoctrineConfigType
     */
    private function getDoctrineConfig(): array
    {
        return [
            'cache'         => [
                'array'      => [
                    'class' => ArrayAdapter::class,
                ],
                'filesystem' => [
                    'class'     => FilesystemAdapter::class,
                    'directory' => getcwd() . '/data/cache',
                    'namespace' => 'doctrine',
                ],
            ],
            'configuration' => [
                'orm_default' => [
                    'entity_listener_resolver' => EntityListenerResolver::class,
                    'result_cache'             => 'filesystem',
                    'metadata_cache'           => 'filesystem',
                    'query_cache'              => 'filesystem',
                    'hydration_cache'          => 'array',
                    'typed_field_mapper'       => null,
                    'second_level_cache'       => [
                        'enabled'                    => true,
                        'default_lifetime'           => 3600,
                        'default_lock_lifetime'      => 60,
                        'file_lock_region_directory' => '',
                        'regions'                    => [],
                    ],
                ],
            ],
            'connection'    => [
                'orm_default' => [
                    'doctrine_mapping_types' => [
                        UuidBinaryType::NAME            => 'binary',
                        UuidBinaryOrderedTimeType::NAME => 'binary',
                    ],
                ],
            ],
            'driver'        => [
                // The default metadata driver aggregates all other drivers into a single one.
                // Override `orm_default` only if you know what you're doing.
                'orm_default' => [
                    'class' => MappingDriverChain::class,
                ],
            ],
            'fixtures'      => getcwd() . '/src/Core/src/App/src/Fixture',
            'migrations'    => [
                'table_storage'           => [
                    'table_name'                 => 'doctrine_migration_versions',
                    'version_column_name'        => 'version',
                    'version_column_length'      => 191,
                    'executed_at_column_name'    => 'executed_at',
                    'execution_time_column_name' => 'execution_time',
                ],
                'migrations_paths'        => [
                    'Core\App\Migration' => 'src/Core/src/App/src/Migration',
                ],
                'all_or_nothing'          => true,
                'check_database_platform' => true,
            ],
            'types'         => [
                UuidType::NAME                  => UuidType::class,
                UuidBinaryType::NAME            => UuidBinaryType::class,
                UuidBinaryOrderedTimeType::NAME => UuidBinaryOrderedTimeType::class,
                SuccessFailureEnumType::NAME    => SuccessFailureEnumType::class,
                YesNoEnumType::NAME             => YesNoEnumType::class,
            ],
        ];
    }
}
