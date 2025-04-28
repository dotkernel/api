<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Role;

use Api\App\Attribute\Resource;
use Api\App\Handler\AbstractHandler;
use Core\User\Entity\UserRole;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserRoleResourceHandler extends AbstractHandler
{
    #[Resource(entity: UserRole::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $request->getAttribute(UserRole::class)
        );
    }
}
