<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Exception\RuntimeException;
use Api\App\Handler\AbstractHandler;
use Core\App\Message;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;

class HandlerDelegatorFactory
{
    /**
     * @param class-string $name
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RuntimeException
     */
    public function __invoke(
        ContainerInterface $container,
        string $name,
        callable $callback
    ): RequestHandlerInterface {
        if (! $container->has(HalResponseFactory::class)) {
            throw RuntimeException::create(Message::serviceNotFound(HalResponseFactory::class));
        }
        if (! $container->has(ResourceGenerator::class)) {
            throw RuntimeException::create(Message::serviceNotFound(ResourceGenerator::class));
        }

        $handler = $callback();
        assert($handler instanceof AbstractHandler);

        return $handler
            ->setResponseFactory($container->get(HalResponseFactory::class))
            ->setResourceGenerator($container->get(ResourceGenerator::class));
    }
}
