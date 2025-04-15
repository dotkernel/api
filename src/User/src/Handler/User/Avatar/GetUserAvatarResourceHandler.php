<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Avatar;

use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
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
        $user = $this->userService->findUser($request->getAttribute('uuid'));
        if (! $user->hasAvatar()) {
            throw NotFoundException::create(Message::USER_AVATAR_MISSING);
        }

        return $this->createResponse($request, $user->getAvatar());
    }
}
