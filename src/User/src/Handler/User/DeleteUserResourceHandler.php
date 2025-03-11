<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

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
        $user = $this->userService->findOneBy(['uuid' => $request->getAttribute('uuid')]);

        $this->userService->deleteUser($user);

        return $this->noContentResponse();
    }
}
