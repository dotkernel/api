<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Role;

use Api\App\Handler\AbstractHandler;
use Api\User\Collection\UserRoleCollection;
use Core\App\Exception\BadRequestException;
use Core\User\Service\UserRoleServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserRoleCollectionHandler extends AbstractHandler
{
    #[Inject(
        UserRoleServiceInterface::class,
    )]
    public function __construct(
        protected UserRoleServiceInterface $userRoleService,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            new UserRoleCollection(
                $this->userRoleService->getUserRoleRepository()->getRoles($request->getQueryParams())
            )
        );
    }
}
