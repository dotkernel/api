<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Entity\EntityListenerResolver;
use Psr\Container\ContainerInterface;

class EntityListenerResolverFactory
{
    public function __invoke(ContainerInterface $container): EntityListenerResolver
    {
        return new EntityListenerResolver($container);
    }
}
