<?php

declare(strict_types=1);

namespace Api\App\Service;

use Api\App\Message;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Filesystem\Filesystem;

class ErrorReportService implements ErrorReportServiceInterface
{
    private FileSystem $fileSystem;
    protected array $config;

    /**
     * @param array $config
     *
     * @Inject({
     *     "config"
     * })
     */
    public function __construct(array $config)
    {
        $this->fileSystem = new Filesystem();
        $this->config = $config[ErrorReportServiceInterface::class] ?? [];
    }

    /**
     * @throws Exception
     */
    public function appendMessage(string $message): void
    {
        if (empty($this->config['path'])) {
            throw new Exception(
                sprintf(
                    Message::MISSING_CONFIG,
                    sprintf('config.%s.path', ErrorReportServiceInterface::class)
                )
            );
        }

        $this->fileSystem->appendToFile(
            $this->config['path'],
            sprintf('%s => %s' . PHP_EOL, date('Y-m-d H:i:s'), $message)
        );
    }

    /**
     * @throws Exception
     */
    public function checkStatus(): self
    {
        if (empty($this->config['enabled'])) {
            throw new Exception(Message::ERROR_REPORT_NOT_ENABLED);
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function checkRequest(ServerRequestInterface $request): self
    {
        if ($this->isMatchingDomain($request)) {
            return $this;
        }

        if ($this->isMatchingIpAddress($request)) {
            return $this;
        }

        throw new Exception(Message::ERROR_REPORT_NOT_ALLOWED);
    }

    private function isMatchingDomain(ServerRequestInterface $request): bool
    {
        if (in_array('*', $this->config['domain_whitelist'])) {
            return true;
        }

        $domain = parse_url($request->getServerParams()['HTTP_ORIGIN'] ?? '', PHP_URL_HOST);

        return in_array($domain, $this->config['domain_whitelist']);
    }

    private function isMatchingIpAddress(ServerRequestInterface $request): bool
    {
        if (in_array('*', $this->config['ip_whitelist'])) {
            return true;
        }

        $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? null;

        return in_array($ipAddress, $this->config['ip_whitelist']);
    }
}
