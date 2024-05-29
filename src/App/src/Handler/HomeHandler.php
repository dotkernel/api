<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomeHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->jsonResponse(['message' => 'Welcome to DotKernel API!']);
    }
}
