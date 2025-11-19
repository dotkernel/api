<?php

declare(strict_types=1);

use Core\App\Doctrine\MigrationsMigratedSubscriber;
use Core\App\Event\TablePrefixEventListener;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Dot\Log\LoggerInterface;
use Psr\Container\ContainerExceptionInterface;

$container = require 'config/container.php';

try {
    $entityManager = $container->get(EntityManager::class);
    $entityManager->getEventManager()
        ->addEventListener(Events::loadClassMetadata, $container->get(TablePrefixEventListener::class));
    $entityManager->getEventManager()
        ->addEventSubscriber(new MigrationsMigratedSubscriber($container));

    return DependencyFactory::fromEntityManager(
        new ConfigurationArray($container->get('config')['doctrine']['migrations']),
        new ExistingEntityManager($entityManager)
    );
} catch (ContainerExceptionInterface $exception) {
    try {
        /** @var LoggerInterface $logger */
        $logger = $container->get('dot-log.default_logger');
        $logger->err($exception->getMessage());
    } catch (ContainerExceptionInterface $exception) {
        error_log($exception->getMessage());
    }
}
