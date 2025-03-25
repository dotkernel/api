<?php

declare(strict_types=1);

namespace Api\App\Service;

use Core\App\Exception\ForbiddenException;
use Core\App\Exception\RuntimeException;
use Core\App\Exception\UnauthorizedException;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Filesystem\Exception\IOException;

interface ErrorReportServiceInterface
{
    /**
     * @throws IOException
     */
    public function appendMessage(string $message): void;

    /**
     * @throws ForbiddenException
     * @throws RuntimeException
     * @throws UnauthorizedException
     */
    public function checkRequest(ServerRequestInterface $request): self;

    public function generateToken(): string;
}
