<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin;

use Api\App\Handler\AbstractHandler;
use Core\Admin\Entity\Admin;
use Core\Admin\Service\AdminServiceInterface;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetAdminResourceHandler extends AbstractHandler
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
        $admin = $this->adminService->getAdminRepository()->find($request->getAttribute('uuid'));
        if (! $admin instanceof Admin) {
            throw new NotFoundException(Message::ADMIN_NOT_FOUND);
        }

        return $this->createResponse($request, $admin);
    }
}
