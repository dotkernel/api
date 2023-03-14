<?php

declare(strict_types=1);

namespace Api\App\Log\Handler;

use Api\App\Exception\ForbiddenException;
use Api\App\Handler\DefaultHandler;
use Api\App\Message;
use Api\App\Service\ErrorReportServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;
use Laminas\Http\Response;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class ErrorReportHandler
 * @package Api\App\Log\Handler
 *
 * @Service
 */
class ErrorReportHandler extends DefaultHandler
{
    private ErrorReportServiceInterface $errorReportService;

    /**
     * ErrorReportHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param ErrorReportServiceInterface $errorReportService
     *
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     ErrorReportServiceInterface::class
     * })
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        ErrorReportServiceInterface $errorReportService
    ) {
        parent::__construct($halResponseFactory, $resourceGenerator);

        $this->errorReportService = $errorReportService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Throwable
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->errorReportService
                ->checkRequest($request)
                ->appendMessage(
                    $request->getParsedBody()['message'] ?? ''
                );
            return $this->infoResponse(Message::ERROR_REPORT_OK);
        } catch (ForbiddenException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::STATUS_CODE_403);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage(), Response::STATUS_CODE_500);
        }
    }
}
