<?php

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Symfony\Component\Console\Helper\HelperSet;

$container = require __DIR__ . '/container.php';
$dbParams = require __DIR__ . '/autoload/db.local.php';

try {
    $connection = DriverManager::getConnection($dbParams['database']);
} catch (Exception $exception) {
    exit($exception->getMessage());
}

/**
 * Fix for the following Doctrine error:
 * Unknown database type enum requested, Doctrine\DBAL\Platforms\MySqlPlatform may not support it.
 *
 * Solution: mapping enum types to string type
 */
$dbPlatform = $connection->getSchemaManager()->getDatabasePlatform();
try {
    $dbPlatform->registerDoctrineTypeMapping('enum', 'string');
} catch (Exception $exception) {
    exit($exception->getMessage());
}

$configuration = new Configuration($connection);
$configuration->setName('DotKernel API Migrations');
$configuration->setMigrationsNamespace('DotKernelApi\Migrations');
$configuration->setMigrationsTableName('migrations');
$configuration->setMigrationsColumnName('version');
$configuration->setMigrationsColumnLength(14);
$configuration->setMigrationsExecutedAtColumnName('executedAt');
$configuration->setMigrationsDirectory('data/doctrine');
$configuration->setAllOrNothing(true);

$ehm = new EntityManagerHelper(
    $container->get('doctrine.entity_manager.orm_default')
);

$helperSet = new HelperSet();
$helperSet->set($ehm, 'em');
$helperSet->set((new ConnectionHelper($connection)), 'db');
$helperSet->set((new ConfigurationHelper($connection, $configuration)));

return $helperSet;