<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Avatar;

use Api\App\Handler\AbstractHandler;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
use Core\User\Entity\User;
use Core\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserAvatarResourceHandler extends AbstractHandler
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
        $user = $this->userService->getUserRepository()->find($request->getAttribute('uuid'));
        if (! $user instanceof User) {
            throw new NotFoundException(Message::USER_NOT_FOUND);
        }
        if (! $user->hasAvatar()) {
            throw new NotFoundException(Message::AVATAR_MISSING);
        }

        return $this->createResponse($request, $user->getAvatar());
    }
}
