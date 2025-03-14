<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Role;

use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserRoleServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserRoleResourceHandler extends AbstractHandler
{
    #[Inject(
        UserRoleServiceInterface::class,
    )]
    public function __construct(
        protected UserRoleServiceInterface $roleService,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $role = $this->roleService->findOneBy(['uuid' => $request->getAttribute('uuid')]);

        return $this->createResponse($request, $role);
    }
}
