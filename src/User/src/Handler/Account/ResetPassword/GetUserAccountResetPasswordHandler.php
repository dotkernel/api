<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\ResetPassword;

use Api\App\Exception\ExpiredException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

class GetUserAccountResetPasswordHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
    ) {
    }

    /**
     * @throws ExpiredException
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash');

        $userResetPassword = $this->userService->findResetPasswordByHash($hash);
        if (! $userResetPassword->isValid()) {
            throw new ExpiredException(sprintf(Message::RESET_PASSWORD_EXPIRED, $hash));
        }
        if ($userResetPassword->isCompleted()) {
            throw new ExpiredException(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        return $this->infoResponse(sprintf(Message::RESET_PASSWORD_VALID, $hash));
    }
}
