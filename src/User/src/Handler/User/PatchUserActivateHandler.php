<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Core\App\Service\MailService;
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userService->findUser($request->getAttribute('uuid'));
        if ($user->isActive()) {
            throw ConflictException::create(Message::USER_ALREADY_ACTIVATED);
        }

        $this->userService->activateUser($user);
        $this->mailService->sendActivationMail($user);

        return $this->infoResponse(Message::USER_ACTIVATED);
    }
}
