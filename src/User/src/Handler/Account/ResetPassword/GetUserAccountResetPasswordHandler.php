<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\ResetPassword;

use Api\App\Exception\ExpiredException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserResetPasswordServiceInterface;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserAccountResetPasswordHandler extends AbstractHandler
{
    #[Inject(
        UserResetPasswordServiceInterface::class,
    )]
    public function __construct(
        protected UserResetPasswordServiceInterface $userResetPasswordService,
    ) {
    }

    /**
     * @throws ExpiredException
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash');

        $userResetPassword = $this->userResetPasswordService->findOneBy(['hash' => $hash]);
        if (! $userResetPassword->isValid()) {
            throw ExpiredException::create(Message::RESET_PASSWORD_EXPIRED);
        }
        if ($userResetPassword->isCompleted()) {
            throw ExpiredException::create(Message::RESET_PASSWORD_USED);
        }

        return $this->infoResponse(Message::RESET_PASSWORD_VALID);
    }
}
