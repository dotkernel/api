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
            RouteListCommand::getDefaultName()     => RouteListCommand::class,
            AdminCreateCommand::getDefaultName()   => AdminCreateCommand::class,
            TokenGenerateCommand::getDefaultName() => TokenGenerateCommand::class,
        ],
    ],
    FileLockerInterface::class => [
        'enabled' => true,
        'dirPath' => getcwd() . '/data/lock',
    ],
];
