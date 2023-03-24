<?php

declare(strict_types=1);

namespace Api\App\Service;

use Api\App\Exception\ForbiddenException;
use Api\App\Message;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Filesystem\Filesystem;

class ErrorReportService implements ErrorReportServiceInterface
{
    private FileSystem $fileSystem;
    private const HEADER_NAME = 'Error-Reporting-Token';
    private ?string $token = null;

    /**
     * @Inject({
     *     "config"
     * })
     */
    public function __construct(
        protected array $config
    ) {
        $this->fileSystem = new Filesystem();
        $this->config = $config[ErrorReportServiceInterface::class] ?? [];
    }

    /**
     * @throws Exception
     */
    public function appendMessage(string $message): void
    {
        $this->fileSystem->appendToFile(
            $this->config['path'],
            sprintf('[%s] [%s] %s' . PHP_EOL, date('Y-m-d H:i:s'), $this->token, $message)
        );
    }

    /**
     * @throws Exception
     */
    public function checkRequest(ServerRequestInterface $request): self
    {
        $this->validateConfigs();

        if (!$this->hasValidToken($request)) {
            throw new ForbiddenException(Message::ERROR_REPORT_NOT_ALLOWED);
        }

        if (!$this->isMatchingDomain($request) && !$this->isMatchingIpAddress($request)) {
            throw new ForbiddenException(Message::ERROR_REPORT_NOT_ALLOWED);
        }

        return $this;
    }

    public function generateToken(): string
    {
        return sha1(uniqid());
    }

    /**
     * @throws Exception
     */
    private function hasValidToken(ServerRequestInterface $request): bool
    {
        $tokens = $request->getHeader(self::HEADER_NAME);
        if (empty($tokens)) {
            return false;
        }

        $this->token = $tokens[0];
        if (!in_array($this->token, $this->config['tokens'])) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    private function isMatchingDomain(ServerRequestInterface $request): bool
    {
        $domain = parse_url($request->getServerParams()['HTTP_ORIGIN'] ?? '', PHP_URL_HOST);
        $intersection = array_intersect($this->config['domain_whitelist'], ['*', $domain]);
        if (empty($intersection)) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    private function isMatchingIpAddress(ServerRequestInterface $request): bool
    {
        $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        $intersection = array_intersect($this->config['ip_whitelist'], ['*', $ipAddress]);
        if (empty($intersection)) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    private function validateConfigs(): void
    {
        if (!array_key_exists('enabled', $this->config)) {
            throw new Exception(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.enabled')
            );
        }

        if ($this->config['enabled'] !== true) {
            throw new Exception(Message::ERROR_REPORT_NOT_ENABLED);
        }

        if (!array_key_exists('path', $this->config)) {
            throw new Exception(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.path')
            );
        }

        if (empty($this->config['path'])) {
            throw new Exception(
                sprintf(Message::INVALID_CONFIG, 'config.ErrorReportServiceInterface::class.path')
            );
        }

        if (!array_key_exists('tokens', $this->config)) {
            throw new Exception(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.tokens')
            );
        }

        if (empty($this->config['tokens'])) {
            throw new Exception(
                sprintf(Message::INVALID_CONFIG, 'config.ErrorReportServiceInterface::class.tokens')
            );
        }

        if (!array_key_exists('domain_whitelist', $this->config)) {
            throw new Exception(
                sprintf(
                    Message::MISSING_CONFIG,
                    sprintf('config.%s.domain_whitelist', ErrorReportServiceInterface::class)
                )
            );
        }

        if (!array_key_exists('ip_whitelist', $this->config)) {
            throw new Exception(
                sprintf(
                    Message::MISSING_CONFIG,
                    sprintf('config.%s.ip_whitelist', ErrorReportServiceInterface::class)
                )
            );
        }
    }
}
