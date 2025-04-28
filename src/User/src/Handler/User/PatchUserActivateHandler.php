<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Attribute\Resource;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Core\App\Service\MailService;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchUserActivateHandler extends AbstractHandler
{
    #[Inject(
        MailService::class,
        UserServiceInterface::class,
    )]
    public function __construct(
        protected MailService $mailService,
        protected UserServiceInterface $userService,
    ) {
    }

    /**
     * @throws ConflictException
     * @throws MailException
     * @throws NotFoundException
     */
    #[Resource(entity: User::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(User::class);
        if ($user->isActive()) {
            throw ConflictException::create(Message::USER_ALREADY_ACTIVATED);
        }

        $this->userService->activateUser($user);
        $this->mailService->sendActivationMail($user);

        return $this->infoResponse(Message::USER_ACTIVATED);
    }
}
