<?php

use Api\Console\App\Handler\RoutesHandler;

/**
 * Documentation: https://github.com/zfcampus/zf-console
 */
return [
    'dot_console' => [
        'name' => 'DotKernel API Console',
        'commands' => [
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
