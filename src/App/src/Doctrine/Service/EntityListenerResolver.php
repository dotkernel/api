<?php

declare(strict_types=1);

namespace Api\App\Doctrine\Service;

use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Psr\Container\ContainerInterface;

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
     * @return mixed
     */
    public function resolve($className)
    {
        return $this->container->get($className);
    }
}
