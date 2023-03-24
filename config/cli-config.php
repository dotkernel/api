<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;

$container = require 'config/container.php';

return DependencyFactory::fromEntityManager(
    new PhpFile('config/migrations.php'),
    new ExistingEntityManager(
        $container->get(EntityManager::class)
    )
);
