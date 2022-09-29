<?php

namespace AppTest\Helper;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Trait Database
 * @package AppTest\Helper
 */
trait DatabaseTrait
{
    public function runMigrations(): void
    {
        $entityManager = $this->getEntityManager();

        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);

        // create the oauth2 tables
        $entityManager->getConnection()->executeQuery('CREATE TABLE oauth_access_tokens (id VARCHAR(100) NOT NULL, user_id VARCHAR(40) DEFAULT NULL, client_id VARCHAR(40) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, scopes TEXT DEFAULT NULL, revoked TINYINT(1) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, expires_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $entityManager->getConnection()->executeQuery('CREATE TABLE oauth_auth_codes (id VARCHAR(100) NOT NULL, user_id INT DEFAULT NULL, client_id INT DEFAULT NULL, scopes TEXT DEFAULT NULL, revoked TINYINT(1) DEFAULT NULL, expires_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $entityManager->getConnection()->executeQuery('CREATE TABLE oauth_clients (name VARCHAR(40) NOT NULL, user_id INT DEFAULT NULL, secret VARCHAR(100) DEFAULT NULL, redirect VARCHAR(255) DEFAULT NULL, personal_access_client TINYINT(1) DEFAULT NULL, password_client TINYINT(1) DEFAULT NULL, revoked TINYINT(1) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(name))');
        $entityManager->getConnection()->executeQuery('CREATE TABLE oauth_personal_access_clients (client_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
        $entityManager->getConnection()->executeQuery('CREATE TABLE oauth_refresh_tokens (id VARCHAR(100) NOT NULL, access_token_id VARCHAR(100) DEFAULT NULL, revoked TINYINT(1) DEFAULT NULL, expires_at DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $entityManager->getConnection()->executeQuery('CREATE TABLE oauth_scopes (id VARCHAR(30), PRIMARY KEY(id))');
    }

    public function runSeeders(): void
    {
        $entityManager = $this->getEntityManager();
        $loader = new Loader();
        $purger = new ORMPurger($entityManager);
        $executor = new ORMExecutor($entityManager, $purger);

        $path = $this->container->get('config')['doctrine']['fixtures'];
        $loader->loadFromDirectory($path);

        $fixtures = $loader->getFixtures();
        $executor->execute($fixtures, true);
    }
}
