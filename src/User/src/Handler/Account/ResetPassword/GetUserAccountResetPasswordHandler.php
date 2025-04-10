<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\ResetPassword;

use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserResetPasswordServiceInterface;
use Core\App\Exception\ExpiredException;
use Core\App\Exception\NotFoundException;
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
            throw new ExpiredException(Message::RESET_PASSWORD_EXPIRED);
        }
        if ($userResetPassword->isCompleted()) {
            throw new ExpiredException(Message::RESET_PASSWORD_USED);
        }

        return $this->infoResponse(Message::RESET_PASSWORD_VALID);
    }
}
