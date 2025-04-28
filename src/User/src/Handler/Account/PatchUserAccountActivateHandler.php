<?php

declare(strict_types=1);

namespace Api\User\Handler\Account;

use Api\App\Attribute\Resource;
use Api\App\Exception\ConflictException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchUserAccountActivateHandler extends AbstractHandler
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
     */
    #[Resource(entity: User::class, identifier: 'hash', placeholder: 'hash')]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(User::class);
        if ($user->isActive()) {
            throw ConflictException::create(Message::USER_ALREADY_ACTIVATED);
        }

        $this->userService->activateUser($user);

        return $this->infoResponse(Message::USER_ACTIVATED);
    }
}
