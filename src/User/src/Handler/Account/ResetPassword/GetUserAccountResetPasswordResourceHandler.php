<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\ResetPassword;

use Api\App\Attribute\Resource;
use Api\App\Exception\ExpiredException;
use Api\App\Handler\AbstractHandler;
use Core\App\Message;
use Core\User\Entity\UserResetPassword;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetUserAccountResetPasswordResourceHandler extends AbstractHandler
{
    /**
     * @throws ExpiredException
     */
    #[Resource(entity: UserResetPassword::class, identifier: 'hash', placeholder: 'hash')]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $userResetPassword = $request->getAttribute(UserResetPassword::class);
        if (! $userResetPassword->isValid()) {
            throw ExpiredException::create(Message::RESET_PASSWORD_EXPIRED);
        }
        if ($userResetPassword->isCompleted()) {
            throw ExpiredException::create(Message::RESET_PASSWORD_USED);
        }

        return $this->infoResponse(Message::RESET_PASSWORD_VALID);
    }
}
