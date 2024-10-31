<?php

declare(strict_types=1);

use Api\Admin\DBAL\Types\AdminStatusEnumType;
use Api\App\Entity\EntityListenerResolver;
use Api\User\DBAL\Types\UserResetPasswordStatusEnumType;
use Api\User\DBAL\Types\UserStatusEnumType;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Dot\Cache\Adapter\ArrayAdapter;
use Dot\Cache\Adapter\FilesystemAdapter;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\Doctrine\UuidType;

return [
    'doctrine' => [
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
                'drivers' => [],
            ],
        ],
        'types'         => [
            UuidType::NAME                        => UuidType::class,
            UuidBinaryType::NAME                  => UuidBinaryType::class,
            UuidBinaryOrderedTimeType::NAME       => UuidBinaryOrderedTimeType::class,
            AdminStatusEnumType::NAME             => AdminStatusEnumType::class,
            UserStatusEnumType::NAME              => UserStatusEnumType::class,
            UserResetPasswordStatusEnumType::NAME => UserResetPasswordStatusEnumType::class,
        ],
        'fixtures'      => getcwd() . '/data/doctrine/fixtures',
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
    ],
];
