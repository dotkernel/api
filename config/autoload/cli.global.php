<?php

declare(strict_types=1);

use Api\Admin\Command\AdminCreateCommand;
use Api\App\Command\TokenGenerateCommand;
use Core\App\Command\RouteListCommand;
use Dot\Cli\FileLockerInterface;

return [
    'dot_cli'                  => [
        'version'  => '1.0.0',
        'name'     => 'Dotkernel CLI',
        'commands' => [
            RouteListCommand::$defaultName     => RouteListCommand::class,
            AdminCreateCommand::$defaultName   => AdminCreateCommand::class,
            TokenGenerateCommand::$defaultName => TokenGenerateCommand::class,
        ],
    ],
    FileLockerInterface::class => [
        'enabled' => true,
        'dirPath' => getcwd() . '/data/lock',
    ],
];
