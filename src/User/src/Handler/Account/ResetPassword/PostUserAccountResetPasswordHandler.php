<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\ResetPassword;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\ResetPasswordInputFilter;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostUserAccountResetPasswordHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
    ) {
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws MailException
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new ResetPasswordInputFilter())->setData((array) $request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        if (! empty($inputFilter->getValue('email'))) {
            $user = $this->userService->findByEmail($inputFilter->getValue('email'));
        } elseif (! empty($inputFilter->getValue('identity'))) {
            $user = $this->userService->findByIdentity($inputFilter->getValue('identity'));
        } else {
            $user = null;
        }

        if (! $user instanceof User) {
            throw new NotFoundException(Message::USER_NOT_FOUND);
        }

        $this->userService->updateUser($user->createResetPassword());
        $this->userService->sendResetPasswordRequestedMail($user);

        return $this->infoResponse(Message::MAIL_SENT_RESET_PASSWORD, StatusCodeInterface::STATUS_CREATED);
    }
}
