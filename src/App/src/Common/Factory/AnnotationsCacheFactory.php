<?php

declare(strict_types=1);

namespace Api\App\Common\Factory;

use Doctrine\Common\Cache\FilesystemCache;
use Psr\Container\ContainerInterface;

/**
 * Class AnnotationsCacheFactory
 * @package Api\App\Common\Factory
 */
class AnnotationsCacheFactory
{
    /**
     * @param ContainerInterface $container
     * @return FilesystemCache
     */
    public function __invoke(ContainerInterface $container): FilesystemCache
    {
        return new FilesystemCache($container->get('config')['annotations_cache_dir']);
    }
}
