<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\Avatar;

use Api\App\Handler\AbstractHandler;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
use Core\User\Entity\User;
use Core\User\Service\UserAvatarServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteUserAccountAvatarHandler extends AbstractHandler
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(User::class);
        if (! $user->hasAvatar()) {
            throw new NotFoundException(Message::AVATAR_MISSING);
        }

        $this->userAvatarService->deleteAvatar($user);

        return $this->noContentResponse();
    }
}
