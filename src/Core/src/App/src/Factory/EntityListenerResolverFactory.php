<?php

declare(strict_types=1);

namespace Core\App\Factory;

use Core\App\Resolver\EntityListenerResolver;
use Psr\Container\ContainerInterface;

class EntityListenerResolverFactory
{
    public function __invoke(ContainerInterface $container): EntityListenerResolver
    {
        return new EntityListenerResolver($container);
    }
}
