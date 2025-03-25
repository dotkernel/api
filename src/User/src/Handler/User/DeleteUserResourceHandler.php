<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Handler\AbstractHandler;
use Core\App\Exception\NotFoundException;
use Core\App\Exception\RuntimeException;
use Core\User\Service\UserServiceInterface;
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

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->userService->deleteUser(
            $this->userService->find($request->getAttribute('uuid'))
        );

        return $this->noContentResponse();
    }
}
