<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserRoleServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserRoleCollectionHandler extends AbstractHandler
{
    #[Inject(
        UserRoleServiceInterface::class,
    )]
    public function __construct(
        protected UserRoleServiceInterface $roleService,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $this->roleService->getRoles($request->getQueryParams()));
    }
}
