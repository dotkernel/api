<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin;

use Api\Admin\Collection\AdminCollection;
use Api\App\Handler\AbstractHandler;
use Core\Admin\Service\AdminServiceInterface;
use Core\App\Exception\BadRequestException;
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

    /**
     * @throws BadRequestException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            new AdminCollection($this->adminService->getAdminRepository()->getAdmins($request->getQueryParams()))
        );
    }
}
