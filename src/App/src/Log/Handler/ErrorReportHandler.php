<?php

declare(strict_types=1);

namespace Api\App\Log\Handler;

use Api\App\Handler\DefaultHandler;
use Api\App\Message;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Filesystem\Filesystem;

use function date;
use function sprintf;

/**
 * Class ErrorReportHandler
 * @package Api\App\Log\Handler
 *
 * @Service
 */
class ErrorReportHandler extends DefaultHandler
{
    protected array $config;

    /**
     * ErrorReportHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param array $config
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, "config.error-report"})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        array $config
    ) {
        parent::__construct($halResponseFactory, $resourceGenerator);

        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (empty($data['message'])) {
            return $this->errorResponse(Message::ERROR_REPORT_KO);
        }

        $writer = new Filesystem();
        $writer->appendToFile(
            $this->config['filePath'],
            sprintf('%s => %s' . PHP_EOL, date('Y-m-d H:i:s'), $data['message'])
        );

        return $this->infoResponse(Message::ERROR_REPORT_OK);
    }
}
