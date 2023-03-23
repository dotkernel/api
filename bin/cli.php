<?php

declare(strict_types=1);

use Dot\Cli\Factory\ApplicationFactory;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$applicationFactory = new ApplicationFactory();
try {
    exit($applicationFactory(require 'config/container.php')->run());
} catch (Exception $e) {
    exit($e->getMessage());
}
