<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Exception\RuntimeException;
use Core\App\Message;
use Dot\ErrorHandler\ErrorHandlerInterface;
use Dot\ErrorHandler\LogErrorHandler;
use Mezzio\ProblemDetails\ProblemDetailsMiddleware;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function assert;

class ProblemDetailsDelegatorFactory
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
    ): ProblemDetailsMiddleware {
        if (! $container->has(ErrorHandlerInterface::class)) {
            throw RuntimeException::create(Message::serviceNotFound(ErrorHandlerInterface::class));
        }

        $problemDetailsMiddleware = $callback();
        assert($problemDetailsMiddleware instanceof ProblemDetailsMiddleware);

        $errorHandler = $container->get(ErrorHandlerInterface::class);
        assert($errorHandler instanceof LogErrorHandler);

        $listener = function (Throwable $throwable, RequestInterface $request) use ($errorHandler) {
            assert($request instanceof ServerRequestInterface);
            $errorHandler->handleThrowable($throwable, $request);
        };

        $problemDetailsMiddleware->attachListener($listener);

        return $problemDetailsMiddleware;
    }
}
