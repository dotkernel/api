<?php

declare(strict_types=1);

namespace Api\App\Log\Handler;

use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;
use Api\App\RestDispatchTrait;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class ErrorReportHandler
 * @package Api\App\Log\Handler
 *
 * @Service
 */
class ErrorReportHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var array $errorReportConfig */
    protected array $errorReportConfig;

    /**
     * ErrorReportHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param array $errorReportConfig
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, "config.error-report"})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        array $errorReportConfig
    ) {
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
        $this->errorReportConfig = $errorReportConfig;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (empty($data['message'])) {
            return $this->infoResponse('<b>message</b> is empty and nothing was saved!');
        }

        $handle = fopen($this->errorReportConfig['filePath'], "a");
        $write = sprintf("%s => %s\r\n", date('Y-m-d H:i:s'), $data['message']);
        fwrite($handle, $write);
        fclose($handle);

        return $this->infoResponse('Error report successfully saved!');
    }
}
