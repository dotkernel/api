<?php

use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\Doctrine\UuidType;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'doctrine_mapping_types' => [
                    UuidBinaryType::NAME => 'binary',
                    UuidBinaryOrderedTimeType::NAME => 'binary',
                ]
            ]
        ],
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'Api\\User\\Entity' => 'UserEntities',
                    'Api\\Admin\\Entity' => 'AdminEntities',
                    'Api\\App\Entity' => 'AppEntities',
                ]
            ],
            'AdminEntities' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/Admin/src/Entity',
            ],
            'UserEntities' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/User/src/Entity',
            ],
            'AppEntities' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/App/src/Entity',
            ]
        ],
        'types' => [
            UuidType::NAME => UuidType::class,
            UuidBinaryType::NAME => UuidBinaryType::class,
            UuidBinaryOrderedTimeType::NAME => UuidBinaryOrderedTimeType::class,
        ],
        'cache' => [
            PhpFileCache::class => [
                'class' => PhpFileCache::class,
                'directory' => getcwd() . '/data/cache/doctrine'
            ]
        ],
        'fixtures' => getcwd() . '/data/doctrine/fixtures',
    ],
    'resultCacheLifetime' => 3600,
];
