<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin\Role;

use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Service\AdminRoleServiceInterface;
use Api\App\Handler\AbstractHandler;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAdminRoleCollectionHandler extends AbstractHandler
{
    #[Inject(
        AdminRoleServiceInterface::class,
    )]
    public function __construct(
        protected AdminRoleServiceInterface $adminRoleService,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            new AdminRoleCollection(
                $this->adminRoleService->getAdminRoles($request->getQueryParams())
            )
        );
    }
}
