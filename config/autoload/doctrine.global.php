<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
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
                    'Api\\User\\Entity' => 'UserEntities'
                ]
            ],
            'UserEntities' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/User/src/Entity',
            ]
        ],
        'types' => [
            UuidType::NAME => UuidType::class,
            UuidBinaryType::NAME => UuidBinaryType::class,
            UuidBinaryOrderedTimeType::NAME => UuidBinaryOrderedTimeType::class,
        ],
        'cache' => [
            \Doctrine\Common\Cache\PhpFileCache::class => [
                'class' => \Doctrine\Common\Cache\PhpFileCache::class,
                'directory' => getcwd() . '/data/cache/doctrine'
            ]
        ]
    ],
    'resultCacheLifetime' => 3600
];
