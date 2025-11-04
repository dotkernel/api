<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Core\App\Event\TablePrefixEventListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Mezzio\Application;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class TablePrefixDelegatorFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $name, callable $callback): Application
    {
        if ($container->has('doctrine.entity_manager.orm_default')) {
            /** @var EntityManagerInterface $entityManager */
            $entityManager = $container->get('doctrine.entity_manager.orm_default');
            $entityManager->getEventManager()->addEventListener(
                Events::loadClassMetadata,
                $container->get(TablePrefixEventListener::class)
            );
        }

        return $callback();
    }
}
