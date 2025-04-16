<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetNotFoundResourceHandler extends AbstractHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->notFoundResponse();
    }
}
