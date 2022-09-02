<?php

declare(strict_types=1);

namespace Api\App\Doctrine\Service;

use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class EntityListenerResolver
 * @package Api\App\Doctrine\Service
 */
class EntityListenerResolver extends DefaultEntityListenerResolver
{
    protected ContainerInterface $container;

    /**
     * EntityListenerResolver constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $className
     * @return object
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resolve($className): object
    {
        return $this->container->get($className);
    }
}
