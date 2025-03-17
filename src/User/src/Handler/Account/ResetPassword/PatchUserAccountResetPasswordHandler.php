<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\ResetPassword;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\ExpiredException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\UpdatePasswordInputFilter;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

class PatchUserAccountResetPasswordHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
        UpdatePasswordInputFilter::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
        protected UpdatePasswordInputFilter $inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws ExpiredException
     * @throws MailException
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
            throw new ConflictException(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        $this->inputFilter->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($this->inputFilter->getMessages());
        }

        $this->userService->updateUser(
            $userResetPassword->markAsCompleted()->getUser(),
            (array) $this->inputFilter->getValues()
        );

        $this->userService->sendResetPasswordCompletedMail($userResetPassword->getUser());

        return $this->infoResponse(Message::RESET_PASSWORD_OK);
    }
}
