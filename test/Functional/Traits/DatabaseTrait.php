<?php

declare(strict_types=1);

namespace ApiTest\Functional\Traits;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait DatabaseTrait
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function runMigrations(): void
    {
        $entityManager = $this->getEntityManager();

        $metaData   = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function runSeeders(): void
    {
        $entityManager = $this->getEntityManager();

        $loader   = new Loader();
        $purger   = new ORMPurger($entityManager);
        $executor = new ORMExecutor($entityManager, $purger);

        $path = $this->getContainer()->get('config')['doctrine']['fixtures'];
        $loader->loadFromDirectory($path);

        $fixtures = $loader->getFixtures();

        foreach ($fixtures as $fixture) {
            $executor->load($entityManager, $fixture);
        }
    }
}
