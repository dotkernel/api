<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\InputFilter\ErrorReportInputFilter;
use Api\App\Service\ErrorReportServiceInterface;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class PostErrorReportResourceHandler extends AbstractHandler
{
    #[Inject(
        ErrorReportServiceInterface::class,
        ErrorReportInputFilter::class,
    )]
    public function __construct(
        protected ErrorReportServiceInterface $errorReportService,
        protected ErrorReportInputFilter $inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     * @throws RuntimeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->inputFilter->validate((array) $request->getParsedBody());
        if (! $result->valid()) {
            throw BadRequestException::fromValidationResult($result);
        }

        $this->errorReportService->appendMessage($result->value()['message']);

        return $this->infoResponse(Message::ERROR_REPORT_OK, StatusCodeInterface::STATUS_CREATED);
    }
}
