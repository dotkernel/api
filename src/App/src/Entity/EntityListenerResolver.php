<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class EntityListenerResolver extends DefaultEntityListenerResolver
{
    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    /**
     * @param string $className
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resolve($className): object
    {
        return $this->container->get($className);
    }
}
