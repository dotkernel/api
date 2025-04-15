<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin\Role;

use Api\Admin\Service\AdminRoleServiceInterface;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAdminRoleResourceHandler extends AbstractHandler
{
    #[Inject(
        AdminRoleServiceInterface::class,
    )]
    public function __construct(
        protected AdminRoleServiceInterface $adminRoleService,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $this->adminRoleService->findAdminRole($request->getAttribute('uuid'))
        );
    }
}
