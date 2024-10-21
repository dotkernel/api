<?php

declare(strict_types=1);

namespace Api\App\Service;

use Api\App\Exception\ForbiddenException;
use Api\App\Exception\RuntimeException;
use Api\App\Exception\UnauthorizedException;
use Api\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

use function array_intersect;
use function array_key_exists;
use function date;
use function in_array;
use function parse_url;
use function sprintf;

use const PHP_EOL;
use const PHP_URL_HOST;

class ErrorReportService implements ErrorReportServiceInterface
{
    private const HEADER_NAME = 'Error-Reporting-Token';

    private Filesystem $fileSystem;
    private string $documentationUrl;
    private string $token = '';

    #[Inject(
        "dot-log.default_logger",
        "config",
    )]
    public function __construct(
        protected LoggerInterface $logger,
        protected array $config,
    ) {
        $this->fileSystem       = new Filesystem();
        $this->config           = $config[ErrorReportServiceInterface::class] ?? [];
        $this->documentationUrl = $this->config['documentation_url'] ?? '';
    }

    /**
     * @throws IOException
     */
    public function appendMessage(string $message): void
    {
        $this->fileSystem->appendToFile($this->config['path'], $this->prepareMessage($message));
    }

    /**
     * @throws ForbiddenException
     * @throws RuntimeException
     * @throws UnauthorizedException
     */
    public function checkRequest(ServerRequestInterface $request): self
    {
        $this->validateConfigs();
        $this->validateToken($request);

        if (! $this->isMatchingDomain($request) && ! $this->isMatchingIpAddress($request)) {
            throw ForbiddenException::create(Message::ERROR_REPORT_NOT_ALLOWED);
        }

        return $this;
    }

    public function generateToken(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * @throws UnauthorizedException
     */
    private function validateToken(ServerRequestInterface $request): void
    {
        $token = $request->getHeaderLine(self::HEADER_NAME);
        if (empty($token) || ! in_array($token, $this->config['tokens'])) {
            throw UnauthorizedException::create(
                sprintf(Message::ERROR_REPORT_UNAUTHORIZED, self::HEADER_NAME),
                $this->documentationUrl
            );
        }

        $this->token = $token;
    }

    private function isMatchingDomain(ServerRequestInterface $request): bool
    {
        $domain = parse_url($request->getServerParams()['HTTP_ORIGIN'] ?? '', PHP_URL_HOST);

        $intersection = array_intersect($this->config['domain_whitelist'], ['*', $domain]);

        return ! empty($intersection);
    }

    private function isMatchingIpAddress(ServerRequestInterface $request): bool
    {
        $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? null;

        $intersection = array_intersect($this->config['ip_whitelist'], ['*', $ipAddress]);

        return ! empty($intersection);
    }

    /**
     * @throws ForbiddenException
     * @throws RuntimeException
     */
    private function validateConfigs(): void
    {
        if (! array_key_exists('enabled', $this->config)) {
            $this->logger->err(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.enabled')
            );
            throw RuntimeException::create(Message::ERROR_REPORT_NOT_CONFIGURED, $this->documentationUrl);
        }

        if ($this->config['enabled'] !== true) {
            $this->logger->err(Message::ERROR_REPORT_NOT_ENABLED);
            throw ForbiddenException::create(Message::ERROR_REPORT_NOT_ENABLED, $this->documentationUrl);
        }

        if (! array_key_exists('path', $this->config)) {
            $this->logger->err(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.path')
            );
            throw RuntimeException::create(Message::ERROR_REPORT_NOT_CONFIGURED, $this->documentationUrl);
        }

        if (empty($this->config['path'])) {
            $this->logger->err(
                sprintf(Message::INVALID_CONFIG, 'config.ErrorReportServiceInterface::class.path')
            );
            throw RuntimeException::create(Message::ERROR_REPORT_NOT_CONFIGURED, $this->documentationUrl);
        }

        if (! array_key_exists('tokens', $this->config)) {
            $this->logger->err(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.tokens')
            );
            throw RuntimeException::create(Message::ERROR_REPORT_NOT_CONFIGURED, $this->documentationUrl);
        }

        if (empty($this->config['tokens'])) {
            $this->logger->err(
                sprintf(Message::INVALID_CONFIG, 'config.ErrorReportServiceInterface::class.tokens')
            );
            throw RuntimeException::create(Message::ERROR_REPORT_NOT_CONFIGURED, $this->documentationUrl);
        }

        if (! array_key_exists('domain_whitelist', $this->config)) {
            $this->logger->err(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.domain_whitelist')
            );
            throw RuntimeException::create(Message::ERROR_REPORT_NOT_CONFIGURED, $this->documentationUrl);
        }

        if (! array_key_exists('ip_whitelist', $this->config)) {
            $this->logger->err(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.ip_whitelist')
            );
            throw RuntimeException::create(Message::ERROR_REPORT_NOT_CONFIGURED, $this->documentationUrl);
        }
    }

    private function prepareMessage(string $message): string
    {
        return sprintf('[%s] [%s] %s' . PHP_EOL, date('Y-m-d H:i:s'), $this->token, $message);
    }
}
