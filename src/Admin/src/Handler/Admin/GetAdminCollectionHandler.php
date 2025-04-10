<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\Handler\AbstractHandler;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAdminCollectionHandler extends AbstractHandler
{
    #[Inject(
        AdminServiceInterface::class,
    )]
    public function __construct(
        protected AdminServiceInterface $adminService,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            new AdminCollection($this->adminService->getAdmins($request->getQueryParams()))
        );
    }
}
