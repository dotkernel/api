<?php

declare(strict_types=1);

namespace Core\App\Resolver;

use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class EntityListenerResolver extends DefaultEntityListenerResolver
{
    public function __construct(
        protected ContainerInterface $container,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resolve(string $className): object
    {
        return $this->container->get($className);
    }
}
