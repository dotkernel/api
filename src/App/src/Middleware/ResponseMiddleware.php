<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Dot\DependencyInjection\Attribute\Inject;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;
use Mezzio\ProblemDetails\ProblemDetailsResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

readonly class ResponseMiddleware implements MiddlewareInterface
{
    #[Inject(
        ProblemDetailsResponseFactory::class,
    )]
    public function __construct(
        private ProblemDetailsResponseFactory $problemDetailsResponseFactory,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ProblemDetailsExceptionInterface $exception) {
            return $this->problemDetailsResponseFactory->createResponseFromThrowable($request, $exception);
        } catch (Throwable $exception) {
            return $this->problemDetailsResponseFactory->createResponse(
                $request,
                StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
                $exception->getMessage()
            );
        }
    }
}
