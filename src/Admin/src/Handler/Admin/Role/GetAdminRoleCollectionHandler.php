<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin\Role;

use Api\Admin\Collection\AdminRoleCollection;
use Api\App\Handler\AbstractHandler;
use Core\Admin\Service\AdminRoleServiceInterface;
use Core\App\Exception\BadRequestException;
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

    /**
     * @throws BadRequestException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            new AdminRoleCollection(
                $this->adminRoleService->getAdminRoleRepository()->getAdminRoles($request->getQueryParams())
            )
        );
    }
}
