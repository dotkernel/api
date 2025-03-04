<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Attribute\MethodDeprecation;
use Api\App\Exception\ForbiddenException;
use Api\App\Exception\UnauthorizedException;
use Api\App\Message;
use Api\App\Service\ErrorReportServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class ErrorReportHandler extends AbstractHandler
{
    #[Inject(
        ErrorReportServiceInterface::class,
    )]
    public function __construct(
        protected ErrorReportServiceInterface $errorReportService,
    ) {
    }

    /**
     * @throws ForbiddenException
     * @throws RuntimeException
     * @throws UnauthorizedException
     */
    #[MethodDeprecation(
        sunset: '2038-01-01',
        link: 'https://docs.dotkernel.org/api-documentation/v5/core-features/versioning',
        deprecationReason: 'Method deprecation example.',
    )]
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $this->errorReportService
            ->checkRequest($request)
            ->appendMessage(
                $request->getParsedBody()['message'] ?? ''
            );

        return $this->infoResponse(Message::ERROR_REPORT_OK, StatusCodeInterface::STATUS_CREATED);
    }
}
