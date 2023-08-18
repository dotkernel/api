<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Exception\ForbiddenException;
use Api\App\Message;
use Api\App\Service\ErrorReportServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;
use Laminas\Http\Response;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * @Service
 */
class ErrorReportHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     ErrorReportServiceInterface::class
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected ErrorReportServiceInterface $errorReportService
    ) {
    }

    /**
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
