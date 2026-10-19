<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\ResetPassword;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\App\Template\RendererInterface;
use Api\User\InputFilter\ResetPasswordInputFilter;
use Api\User\Service\UserResetPasswordServiceInterface;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Core\App\Service\MailService;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostUserAccountResetPasswordResourceHandler extends AbstractHandler
{
    #[Inject(
        MailService::class,
        UserServiceInterface::class,
        UserResetPasswordServiceInterface::class,
        ResetPasswordInputFilter::class,
        RendererInterface::class,
    )]
    public function __construct(
        protected MailService $mailService,
        protected UserServiceInterface $userService,
        protected UserResetPasswordServiceInterface $userResetPasswordService,
        protected ResetPasswordInputFilter $inputFilter,
        protected RendererInterface $renderer,
    ) {
    }

    /**
     * @throws BadRequestException
     * @throws MailException
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->inputFilter->validate((array) $request->getParsedBody());
        if (! $result->valid()) {
            throw BadRequestException::fromValidationResult($result);
        }

        $data = $result->value();
        if (! empty($data['email'])) {
            $user = $this->userService->findByEmail($data['email']);
        } elseif (! empty($data['identity'])) {
            $user = $this->userService->findByIdentity($data['identity']);
        } else {
            throw NotFoundException::create(Message::USER_NOT_FOUND);
        }

        $resetPassword = $this->userResetPasswordService->saveResetPassword($user);
        $this->mailService->sendResetPasswordRequestedMail(
            $user,
            $this->renderer->render('user::reset-password-requested', [
                'user'          => $user,
                'resetPassword' => $resetPassword,
            ])
        );

        return $this->infoResponse(Message::MAIL_SENT_RESET_PASSWORD, StatusCodeInterface::STATUS_CREATED);
    }
}
