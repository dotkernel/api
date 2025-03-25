<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin\Role;

use Api\App\Handler\AbstractHandler;
use Core\Admin\Entity\AdminRole;
use Core\Admin\Service\AdminRoleServiceInterface;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
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
        $adminRole = $this->adminRoleService->getAdminRoleRepository()->find($request->getAttribute('uuid'));
        if (! $adminRole instanceof AdminRole) {
            throw new NotFoundException(Message::ROLE_NOT_FOUND);
        }

        return $this->createResponse($request, $adminRole);
    }
}
