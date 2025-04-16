<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin\Role;

use Api\App\Attribute\Resource;
use Api\App\Handler\AbstractHandler;
use Core\Admin\Entity\AdminRole;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAdminRoleResourceHandler extends AbstractHandler
{
    #[Resource(entity: AdminRole::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $request->getAttribute(AdminRole::class)
        );
    }
}
