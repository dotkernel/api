<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Avatar;

use Api\App\Attribute\Resource;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserAvatarServiceInterface;
use Core\App\Message;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteUserAvatarResourceHandler extends AbstractHandler
{
    #[Inject(
        UserAvatarServiceInterface::class,
    )]
    public function __construct(
        protected UserAvatarServiceInterface $userAvatarService,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    #[Resource(entity: User::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(User::class);
        if (! $user->hasAvatar()) {
            throw NotFoundException::create(Message::USER_AVATAR_MISSING);
        }

        $this->userAvatarService->deleteAvatar($user);

        return $this->noContentResponse();
    }
}
