<?php

declare(strict_types=1);

namespace Api\App\Service;

use Api\App\Exception\ForbiddenException;
use Api\App\Exception\RuntimeException;
use Api\App\Exception\UnauthorizedException;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ServerRequestInterface;
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

    /**
     * @param array<non-empty-string, mixed> $config
     */
    #[Inject(
        'config',
    )]
    public function __construct(
        protected array $config,
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
            throw ForbiddenException::create(Message::ERROR_REPORT_NOT_ALLOWED);
        }

        return $this;
    }

    public function generateToken(): string
    {
        return sha1(uniqid());
    }

    /**
     * @throws ForbiddenException
     * @throws UnauthorizedException
     */
    private function validateToken(ServerRequestInterface $request): void
    {
        $this->token = $request->getHeaderLine(self::HEADER_NAME);
        if (empty($this->token)) {
            throw UnauthorizedException::create(
                Message::ERROR_REPORT_NOT_ALLOWED,
                $this->config['documentation_url'] ?? ''
            );
        }

        if (! in_array($this->token, $this->config['tokens'])) {
            throw ForbiddenException::create(Message::ERROR_REPORT_NOT_ALLOWED);
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
            throw RuntimeException::create(
                Message::missingConfig('config.ErrorReportServiceInterface::class.enabled')
            );
        }

        if ($this->config['enabled'] !== true) {
            throw RuntimeException::create(Message::ERROR_REPORT_NOT_ENABLED);
        }

        if (! array_key_exists('path', $this->config)) {
            throw RuntimeException::create(
                Message::missingConfig('config.ErrorReportServiceInterface::class.path')
            );
        }

        if (empty($this->config['path'])) {
            throw RuntimeException::create(
                Message::invalidConfig('config.ErrorReportServiceInterface::class.path')
            );
        }

        if (! array_key_exists('tokens', $this->config)) {
            throw RuntimeException::create(
                Message::missingConfig('config.ErrorReportServiceInterface::class.tokens')
            );
        }

        if (empty($this->config['tokens'])) {
            throw RuntimeException::create(
                Message::invalidConfig('config.ErrorReportServiceInterface::class.tokens')
            );
        }

        if (! array_key_exists('domain_whitelist', $this->config)) {
            throw RuntimeException::create(
                Message::missingConfig('config.ErrorReportServiceInterface::class.domain_whitelist')
            );
        }

        if (! array_key_exists('ip_whitelist', $this->config)) {
            throw RuntimeException::create(
                Message::missingConfig('config.ErrorReportServiceInterface::class.ip_whitelist')
            );
        }
    }
}
