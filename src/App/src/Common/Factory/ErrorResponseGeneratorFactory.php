<?php

declare(strict_types=1);

namespace Api\App\Common\Factory;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Middleware\ErrorResponseGenerator;

/**
 * Class ErrorResponseGeneratorFactory
 * @package Api\App\Common\Factory
 */
class ErrorResponseGeneratorFactory
{
    /**
     * @param ContainerInterface $container
     * @return ErrorResponseGenerator
     */
    public function __invoke(ContainerInterface $container): ErrorResponseGenerator
    {
        $config = $container->has('config') ? $container->get('config') : [];

        return new ErrorResponseGenerator(($config['debug'] ?? false));
    }
}
