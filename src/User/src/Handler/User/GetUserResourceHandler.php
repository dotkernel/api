<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserServiceInterface;
use Core\App\Exception\NotFoundException;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserResourceHandler extends AbstractHandler
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
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $this->userService->findUser($request->getAttribute('uuid'))
        );
    }
}
