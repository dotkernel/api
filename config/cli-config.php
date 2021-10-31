<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

$container = require 'config/container.php';

return ConsoleRunner::createHelperSet(
    $container->get('doctrine.entity_manager.orm_default')
);
