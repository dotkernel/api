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
