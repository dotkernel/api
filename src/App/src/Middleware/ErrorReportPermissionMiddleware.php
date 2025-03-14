<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\App\Exception\ForbiddenException;
use Api\App\Exception\UnauthorizedException;
use Api\App\Service\ErrorReportServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorReportPermissionMiddleware implements MiddlewareInterface
{
    #[Inject(
        ErrorReportServiceInterface::class,
    )]
    public function __construct(
        protected ErrorReportServiceInterface $errorReportService,
    ) {
    }

    /**
     * @throws UnauthorizedException
     * @throws ForbiddenException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->errorReportService->checkRequest($request);

        return $handler->handle($request);
    }
}
