<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin;

use Api\Admin\Service\AdminServiceInterface;
use Api\App\Attribute\Resource;
use Api\App\Handler\AbstractHandler;
use Core\Admin\Entity\Admin;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteAdminResourceHandler extends AbstractHandler
{
    #[Inject(
        AdminServiceInterface::class,
    )]
    public function __construct(
        protected AdminServiceInterface $adminService,
    ) {
    }

    #[Resource(entity: Admin::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->adminService->deleteAdmin(
            $request->getAttribute(Admin::class)
        );

        return $this->noContentResponse();
    }
}
