<?php

declare(strict_types=1);

namespace Core\App;

use Api\App\Factory\EntityListenerResolverFactory;
use Core\App\Entity\EntityListenerResolver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Dot\Cache\Adapter\ArrayAdapter;
use Dot\Cache\Adapter\FilesystemAdapter;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\Doctrine\UuidType;
use Roave\PsrContainerDoctrine\EntityManagerFactory;

use function getcwd;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'        => $this->getDependencies(),
            'doctrine'            => $this->getDoctrineConfig(),
            'resultCacheLifetime' => 600,
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                'doctrine.entity_manager.orm_default' => EntityManagerFactory::class,
                EntityListenerResolver::class         => EntityListenerResolverFactory::class,
            ],
            'aliases'   => [
                EntityManager::class          => 'doctrine.entity_manager.orm_default',
                EntityManagerInterface::class => 'doctrine.entity_manager.orm_default',
            ],
        ];
    }

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
                'orm_default' => [
                    'class'   => MappingDriverChain::class,
                    'drivers' => [
                        'Core\App\Entity' => 'AppEntities',
                    ],
                ],
                'AppEntities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => getcwd() . '/src/Core/src/App/src/Entity',
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
            ],
        ];
    }
}
