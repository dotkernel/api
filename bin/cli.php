<?php

declare(strict_types=1);

use Dot\Cli\Factory\ApplicationFactory;
use Psr\Container\ContainerInterface;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require 'config/container.php';

$applicationFactory = new ApplicationFactory();
try {
    exit($applicationFactory($container)->run());
} catch (Exception $e) {
    exit($e->getMessage());
}
