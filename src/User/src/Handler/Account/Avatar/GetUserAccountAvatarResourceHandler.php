<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\Avatar;

use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\App\IdentityInterface;
use Api\User\Service\UserAvatarServiceInterface;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserAccountAvatarResourceHandler extends AbstractHandler
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
        $user = $request->getAttribute(IdentityInterface::class);
        if (! $user->hasAvatar()) {
            throw NotFoundException::create(Message::USER_AVATAR_MISSING);
        }

        return $this->createResponse($request, $user->getAvatar());
    }
}
