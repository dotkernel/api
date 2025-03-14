<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Attribute\MethodDeprecation;
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
    #[MethodDeprecation(
        sunset: '2038-01-01',
        link: 'https://docs.dotkernel.org/api-documentation/v5/core-features/versioning',
        deprecationReason: 'Method deprecation example.',
    )]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($this->inputFilter->getMessages());
        }

        $this->errorReportService->appendMessage($this->inputFilter->getValue('message'));

        return $this->infoResponse(Message::ERROR_REPORT_OK, StatusCodeInterface::STATUS_CREATED);
    }
}
