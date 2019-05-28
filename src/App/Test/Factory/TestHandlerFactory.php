<?php

declare(strict_types=1);

namespace App\Test\Factory;

use App\Test\Handler\TestHandler;
use Psr\Container\ContainerInterface;

/**
 * Class TestHandlerFactory
 * @package App\Test\Factory
 */
class TestHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return TestHandler
     */
    public function __invoke(ContainerInterface $container) : TestHandler
    {
        return new TestHandler(
            $container->get('doctrine.entity_manager.orm_default')
        );
    }
}
