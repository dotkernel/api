<?php

declare(strict_types=1);

namespace Api\App\Doctrine\Factory;

use Api\App\Doctrine\Service\EntityListenerResolver;
use Psr\Container\ContainerInterface;

/**
 * Class EntityListenerResolverFactory
 * @package Api\App\Doctrine\Factory
 */
class EntityListenerResolverFactory
{
    /**
     * @param ContainerInterface $container
     * @return EntityListenerResolver
     */
    public function __invoke(ContainerInterface $container)
    {
        return new EntityListenerResolver($container);
    }
}
