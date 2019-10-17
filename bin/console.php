<?php
/**
 * Console application bootstrap file
 */

use Interop\Container\ContainerInterface;
use ZF\Console\Application;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
call_user_func(function () {
    /** @var ContainerInterface $container */
    $container = require 'config/container.php';

    /** @var Application $app */
    $app = $container->get(Application::class);
    $app->setDebug(false);

    $exit = $app->run();
    exit($exit);
});
