<?php

declare(strict_types=1);

use Api\App\Entity\EntityListenerResolver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Dot\Cache\Adapter\ArrayAdapter;
use Dot\Cache\Adapter\FilesystemAdapter;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\Doctrine\UuidType;

return [
    'doctrine'            => [
        'connection' => [
            'orm_default' => [
                'doctrine_mapping_types' => [
                    UuidBinaryType::NAME            => 'binary',
                    UuidBinaryOrderedTimeType::NAME => 'binary',
                ],
            ],
        ],
        'driver'     => [
            'orm_default'   => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'Api\\User\\Entity'  => 'UserEntities',
                    'Api\\Admin\\Entity' => 'AdminEntities',
                    'Api\\App\Entity'    => 'AppEntities',
                ],
            ],
            'AdminEntities' => [
                'class' => AttributeDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/Admin/src/Entity',
            ],
            'UserEntities'  => [
                'class' => AttributeDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/User/src/Entity',
            ],
            'AppEntities'   => [
                'class' => AttributeDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/App/src/Entity',
            ],
        ],
        'types'      => [
            UuidType::NAME                  => UuidType::class,
            UuidBinaryType::NAME            => UuidBinaryType::class,
            UuidBinaryOrderedTimeType::NAME => UuidBinaryOrderedTimeType::class,
        ],
        'fixtures'   => getcwd() . '/data/doctrine/fixtures',
        'configuration' => [
            'orm_default' => [
                'entity_listener_resolver' => EntityListenerResolver::class,
                'result_cache'       => 'filesystem',
                'metadata_cache'     => 'filesystem',
                'query_cache'        => 'filesystem',
                'hydration_cache'    => 'array',
                'second_level_cache' => [
                    'enabled'                    => true,
                    'default_lifetime'           => 3600,
                    'default_lock_lifetime'      => 60,
                    'file_lock_region_directory' => '',
                    'regions'                    => [],
                ],
            ],
        ],
        'cache' => [
            'array' => [
                'class'     => ArrayAdapter::class,
            ],
            'filesystem' => [
                'class'     => FilesystemAdapter::class,
                'directory' => getcwd() . '/data/cache',
                'namespace' => 'doctrine',
            ],
        ],
    ],
    'resultCacheLifetime' => 3600,
];
