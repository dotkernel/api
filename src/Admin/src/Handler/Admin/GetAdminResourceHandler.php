<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin;

use Api\App\Attribute\Resource;
use Api\App\Handler\AbstractHandler;
use Core\Admin\Entity\Admin;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAdminResourceHandler extends AbstractHandler
{
    #[Resource(entity: Admin::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $request->getAttribute(Admin::class)
        );
    }
}
