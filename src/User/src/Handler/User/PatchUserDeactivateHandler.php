<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserServiceInterface;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchUserDeactivateHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
    ) {
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userService->findUser($request->getAttribute('uuid'));
        if ($user->isPending()) {
            throw new ConflictException(Message::USER_ALREADY_DEACTIVATED);
        }

        $this->userService->deactivateUser($user);

        return $this->infoResponse(Message::USER_DEACTIVATED);
    }
}
