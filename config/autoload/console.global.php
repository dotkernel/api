<?php

use Api\Console\App\Handler\RoutesHandler;
use Api\Console\User\Handler\ListUsersHandler;
use Api\User\Entity\UserEntity;

/**
 * Documentation: https://github.com/zfcampus/zf-console
 */
return [
    'dot_console' => [
        'name' => 'DotKernel API Console',
        'commands' => [
            [
                // php bin/console.php user:list
                'name' => 'user:list',
                'route' => '[--page=] [--search=] [--status=] [--deleted=]',
                'description' => 'List all users based on a set of optional filters.',
                'short_description' => 'List users.',
                'options_descriptions' => [
                    'page' => '(Optional) Page number',
                    'search' => '(Optional) Filter users by search string.',
                    'status' => '(Optional) Filter users by status. (' . implode(', ', UserEntity::STATUSES) . ')',
                    'deleted' => '(Optional) Filter users by deletion status (true, false)'
                ],
                'defaults' => ['page' => 1, 'search' => null, 'status' => null, 'deleted' => null],
                'handler' => ListUsersHandler::class
            ],
            [
                // php bin/console.php route:list
                'name' => 'route:list',
                'description' => 'List all routes in alphabetical order.',
                'short_description' => 'List routes.',
                'handler' => RoutesHandler::class
            ]
        ]
    ]
];
