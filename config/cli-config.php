<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Helper\HelperSet;

$container = require __DIR__ . '/container.php';

try {
    $connection = DriverManager::getConnection($container->get('config')['databases']['default'] ?? null);
} catch (Exception $exception) {
    exit($exception->getMessage());
}

$configuration = new Configuration($connection);
$configuration->setCheckDatabasePlatform(false);
$configuration->setName('DotKernel API Migrations');
$configuration->setMigrationsNamespace('DotKernelApi\Migrations');
$configuration->setMigrationsTableName('migrations');
$configuration->setMigrationsColumnName('version');
$configuration->setMigrationsColumnLength(14);
$configuration->setMigrationsExecutedAtColumnName('executedAt');
$configuration->setMigrationsDirectory('data/doctrine/migrations');
$configuration->setAllOrNothing(true);

$helperSet = new HelperSet();
$helperSet->set((new EntityManagerHelper($container->get(EntityManager::class))), 'em');
$helperSet->set((new ConnectionHelper($connection)), 'db');
$helperSet->set((new ConfigurationHelper($connection, $configuration)));

return $helperSet;
