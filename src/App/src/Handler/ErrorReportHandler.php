<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Exception\ForbiddenException;
use Api\App\Message;
use Api\App\Service\ErrorReportServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * @Service
 */
class ErrorReportHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     ErrorReportServiceInterface::class,
     *     "config"
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected ErrorReportServiceInterface $errorReportService,
        protected array $config,
    ) {
    }

    /**
     * @throws ForbiddenException
     * @throws RuntimeException
     */
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
