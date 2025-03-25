<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Role;

use Api\App\Handler\AbstractHandler;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
use Core\User\Entity\UserRole;
use Core\User\Service\UserRoleServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserRoleResourceHandler extends AbstractHandler
{
    #[Inject(
        UserRoleServiceInterface::class,
    )]
    public function __construct(
        protected UserRoleServiceInterface $userRoleService,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userRole = $this->userRoleService->getRoleRepository()->find($request->getAttribute('uuid'));
        if (! $userRole instanceof UserRole) {
            throw new NotFoundException(Message::ROLE_NOT_FOUND);
        }

        return $this->createResponse($request, $userRole);
    }
}
