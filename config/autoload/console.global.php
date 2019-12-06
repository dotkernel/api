<?php

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
                // command: php bin/console.php list-users
                'name' => 'list-users',
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
            ]
        ]
    ]
];
