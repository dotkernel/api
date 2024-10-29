<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Exception\RuntimeException;
use Api\App\Handler\AbstractHandler;
use Api\App\Message;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;
use function sprintf;

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
            throw new RuntimeException(sprintf(Message::SERVICE_NOT_FOUND, HalResponseFactory::class));
        }
        if (! $container->has(ResourceGenerator::class)) {
            throw new RuntimeException(sprintf(Message::SERVICE_NOT_FOUND, ResourceGenerator::class));
        }

        $handler = $callback();
        assert($handler instanceof AbstractHandler);

        return $handler
            ->setResponseFactory($container->get(HalResponseFactory::class))
            ->setResourceGenerator($container->get(ResourceGenerator::class));
    }
}
