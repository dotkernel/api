<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Role;

use Api\App\Handler\AbstractHandler;
use Core\App\Exception\NotFoundException;
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
        return $this->createResponse(
            $request,
            $this->userRoleService->find($request->getAttribute('uuid'))
        );
    }
}
