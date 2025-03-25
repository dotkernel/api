<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin;

use Api\App\Handler\AbstractHandler;
use Core\Admin\Service\AdminServiceInterface;
use Core\App\Exception\NotFoundException;
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

    /**
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->adminService->getAdminRepository()->deleteAdmin(
            $this->adminService->find($request->getAttribute('uuid'))
        );

        return $this->noContentResponse();
    }
}
