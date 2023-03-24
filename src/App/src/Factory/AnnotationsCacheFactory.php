<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Doctrine\Common\Cache\FilesystemCache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AnnotationsCacheFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FilesystemCache
    {
        return new FilesystemCache($container->get('config')['annotations_cache_dir']);
    }
}
