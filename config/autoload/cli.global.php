<?php

declare(strict_types=1);

use Api\Admin\Command\AdminCreateCommand;
use Api\App\Command\RouteListCommand;
use Dot\Cli\Command\DemoCommand;
use Dot\Cli\FileLockerInterface;

/**
 * Documentation: https://docs.laminas.dev/laminas-cli/
 */
return [
    'dot_cli' => [
        'version' => '1.0.0',
        'name' => 'DotKernel CLI',
        'commands' => [
            DemoCommand::getDefaultName() => DemoCommand::class,
            RouteListCommand::getDefaultName() => RouteListCommand::class,
            AdminCreateCommand::getDefaultName() => AdminCreateCommand::class
        ]
    ],
    FileLockerInterface::class => [
        'enabled' => true,
        'dirPath' => getcwd() . '/data/lock',
    ]
];
