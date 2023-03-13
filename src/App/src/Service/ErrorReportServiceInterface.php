<?php

declare(strict_types=1);

namespace Api\App\Service;

use Exception;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorReportServiceInterface
{
    /**
     * @throws Exception
     */
    public function appendMessage(string $message): void;

    /**
     * @throws Exception
     */
    public function checkRequest(ServerRequestInterface $request): self;

    public function generateToken(): string;
}
