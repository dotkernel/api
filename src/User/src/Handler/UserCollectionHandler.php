<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserCollectionHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $this->userService->getUsers($request->getQueryParams()));
    }
}
