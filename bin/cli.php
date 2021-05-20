<?php

declare(strict_types=1);

use Dot\Cli\Factory\ApplicationFactory;
use Psr\Container\ContainerInterface;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require 'config/container.php';

$applicationFactory = new ApplicationFactory();
exit($applicationFactory($container)->run());
