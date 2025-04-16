<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\ResetPassword;

use Api\App\Attribute\Resource;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\ExpiredException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\UpdatePasswordInputFilter;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Core\App\Service\MailService;
use Core\User\Entity\UserResetPassword;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchUserAccountResetPasswordResourceHandler extends AbstractHandler
{
    #[Inject(
        MailService::class,
        UserServiceInterface::class,
        UpdatePasswordInputFilter::class,
    )]
    public function __construct(
        protected MailService $mailService,
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
    #[Resource(entity: UserResetPassword::class, identifier: 'hash', placeholder: 'hash')]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw BadRequestException::create(
                detail: Message::VALIDATOR_INVALID_DATA,
                additional: ['errors' => $this->inputFilter->getMessages()]
            );
        }

        $userResetPassword = $request->getAttribute(UserResetPassword::class);
        if (! $userResetPassword->isValid()) {
            throw ExpiredException::create(Message::RESET_PASSWORD_EXPIRED);
        }
        if ($userResetPassword->isCompleted()) {
            throw ConflictException::create(Message::RESET_PASSWORD_USED);
        }

        $this->userService->saveUser(
            (array) $this->inputFilter->getValues(),
            $userResetPassword->markAsCompleted()->getUser()
        );

        $this->mailService->sendResetPasswordCompletedMail($userResetPassword->getUser());

        return $this->infoResponse(Message::RESET_PASSWORD_OK);
    }
}
