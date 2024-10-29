<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Service\AdminRoleServiceInterface;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminRoleHandler extends AbstractHandler
{
    #[Inject(
        AdminRoleServiceInterface::class,
    )]
    public function __construct(
        protected AdminRoleServiceInterface $roleService,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $role = $this->roleService->findOneBy(['uuid' => $request->getAttribute('uuid')]);

        return $this->createResponse($request, $role);
    }
}
