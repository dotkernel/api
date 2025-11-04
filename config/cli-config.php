<?php

declare(strict_types=1);

use Core\App\Event\TablePrefixEventListener;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;

$container = require 'config/container.php';

$entityManager = $container->get(EntityManager::class);
$entityManager->getEventManager()
    ->addEventListener(Events::loadClassMetadata, $container->get(TablePrefixEventListener::class));

return DependencyFactory::fromEntityManager(
    new ConfigurationArray($container->get('config')['doctrine']['migrations']),
    new ExistingEntityManager($entityManager)
);
