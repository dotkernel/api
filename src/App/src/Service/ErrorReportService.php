<?php

declare(strict_types=1);

namespace Api\App\Service;

use Api\App\Exception\ForbiddenException;
use Api\App\Exception\UnauthorizedException;
use Api\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

use function array_intersect;
use function array_key_exists;
use function date;
use function in_array;
use function parse_url;
use function sha1;
use function sprintf;
use function uniqid;

use const PHP_EOL;
use const PHP_URL_HOST;

class ErrorReportService implements ErrorReportServiceInterface
{
    private const HEADER_NAME = 'Error-Reporting-Token';
    private Filesystem $fileSystem;
    private ?string $token = null;

    #[Inject(
        "config",
    )]
    public function __construct(
        protected array $config
    ) {
        $this->fileSystem = new Filesystem();
        $this->config     = $config[ErrorReportServiceInterface::class] ?? [];
    }

    /**
     * @throws IOException
     */
    public function appendMessage(string $message): void
    {
        $this->fileSystem->appendToFile(
            $this->config['path'],
            sprintf('[%s] [%s] %s' . PHP_EOL, date('Y-m-d H:i:s'), (string) $this->token, $message)
        );
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
            throw new ForbiddenException(Message::ERROR_REPORT_NOT_ALLOWED);
        }

        return $this;
    }

    public function generateToken(): string
    {
        return sha1(uniqid());
    }

    /**
     * @throws UnauthorizedException
     * @throws ForbiddenException
     */
    private function validateToken(ServerRequestInterface $request): void
    {
        $this->token = $request->getHeaderLine(self::HEADER_NAME);
        if (empty($this->token)) {
            throw new UnauthorizedException(Message::ERROR_REPORT_NOT_ALLOWED);
        }

        if (! in_array($this->token, $this->config['tokens'])) {
            throw new ForbiddenException(Message::ERROR_REPORT_NOT_ALLOWED);
        }
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
     * @throws RuntimeException
     */
    private function validateConfigs(): void
    {
        if (! array_key_exists('enabled', $this->config)) {
            throw new RuntimeException(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.enabled')
            );
        }

        if ($this->config['enabled'] !== true) {
            throw new RuntimeException(Message::ERROR_REPORT_NOT_ENABLED);
        }

        if (! array_key_exists('path', $this->config)) {
            throw new RuntimeException(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.path')
            );
        }

        if (empty($this->config['path'])) {
            throw new RuntimeException(
                sprintf(Message::INVALID_CONFIG, 'config.ErrorReportServiceInterface::class.path')
            );
        }

        if (! array_key_exists('tokens', $this->config)) {
            throw new RuntimeException(
                sprintf(Message::MISSING_CONFIG, 'config.ErrorReportServiceInterface::class.tokens')
            );
        }

        if (empty($this->config['tokens'])) {
            throw new RuntimeException(
                sprintf(Message::INVALID_CONFIG, 'config.ErrorReportServiceInterface::class.tokens')
            );
        }

        if (! array_key_exists('domain_whitelist', $this->config)) {
            throw new RuntimeException(
                sprintf(
                    Message::MISSING_CONFIG,
                    sprintf('config.%s.domain_whitelist', ErrorReportServiceInterface::class)
                )
            );
        }

        if (! array_key_exists('ip_whitelist', $this->config)) {
            throw new RuntimeException(
                sprintf(
                    Message::MISSING_CONFIG,
                    sprintf('config.%s.ip_whitelist', ErrorReportServiceInterface::class)
                )
            );
        }
    }
}
