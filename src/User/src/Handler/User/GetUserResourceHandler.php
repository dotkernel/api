<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Attribute\Resource;
use Api\App\Handler\AbstractHandler;
use Core\User\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserResourceHandler extends AbstractHandler
{
    #[Resource(entity: User::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $request->getAttribute(User::class)
        );
    }
}
