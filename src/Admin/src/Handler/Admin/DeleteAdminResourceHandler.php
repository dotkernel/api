<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin;

use Api\Admin\Service\AdminServiceInterface;
use Api\App\Handler\AbstractHandler;
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
        $this->adminService->deleteAdmin($this->adminService->findAdmin($request->getAttribute('uuid')));

        return $this->noContentResponse();
    }
}
