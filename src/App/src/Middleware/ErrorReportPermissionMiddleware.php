<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\App\Service\ErrorReportServiceInterface;
use Core\App\Exception\ForbiddenException;
use Core\App\Exception\RuntimeException;
use Core\App\Exception\UnauthorizedException;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class ErrorReportPermissionMiddleware implements MiddlewareInterface
{
    #[Inject(
        ErrorReportServiceInterface::class,
    )]
    public function __construct(
        private ErrorReportServiceInterface $errorReportService,
    ) {
    }

    /**
     * @throws ForbiddenException
     * @throws RuntimeException
     * @throws UnauthorizedException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->errorReportService->checkRequest($request);

        return $handler->handle($request);
    }
}
