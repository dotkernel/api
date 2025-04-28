<?php

declare(strict_types=1);

namespace Api\User\Handler\Account;

use Api\App\Handler\AbstractHandler;
use Api\App\IdentityInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserAccountResourceHandler extends AbstractHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $request->getAttribute(IdentityInterface::class));
    }
}
