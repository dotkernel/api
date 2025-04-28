<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Attribute\Resource;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserServiceInterface;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteUserResourceHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
    ) {
    }

    #[Resource(entity: User::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->userService->deleteUser(
            $request->getAttribute(User::class)
        );

        return $this->noContentResponse();
    }
}
