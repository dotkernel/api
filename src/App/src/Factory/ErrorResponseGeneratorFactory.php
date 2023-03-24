<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Mezzio\Middleware\ErrorResponseGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ErrorResponseGeneratorFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ErrorResponseGenerator
    {
        $config = $container->has('config') ? $container->get('config') : [];

        return new ErrorResponseGenerator(($config['debug'] ?? false));
    }
}
