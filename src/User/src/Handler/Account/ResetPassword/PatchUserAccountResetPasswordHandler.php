<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\ResetPassword;

use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\UpdatePasswordInputFilter;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\ExpiredException;
use Core\App\Exception\NotFoundException;
use Core\App\Message;
use Core\App\Service\MailService;
use Core\User\Service\UserResetPasswordServiceInterface;
use Core\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

class PatchUserAccountResetPasswordHandler extends AbstractHandler
{
    #[Inject(
        MailService::class,
        UserServiceInterface::class,
        UserResetPasswordServiceInterface::class,
        UpdatePasswordInputFilter::class,
    )]
    public function __construct(
        protected MailService $mailService,
        protected UserServiceInterface $userService,
        protected UserResetPasswordServiceInterface $userResetPasswordService,
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
        $this->inputFilter->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($this->inputFilter->getMessages());
        }

        $hash = $request->getAttribute('hash');

        $userResetPassword = $this->userResetPasswordService->findOneBy(['hash' => $hash]);
        if (! $userResetPassword->isValid()) {
            throw new ExpiredException(sprintf(Message::RESET_PASSWORD_EXPIRED, $hash));
        }
        if ($userResetPassword->isCompleted()) {
            throw new ConflictException(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        $this->userService->updateUser(
            $userResetPassword->markAsCompleted()->getUser(),
            (array) $this->inputFilter->getValues()
        );

        $this->mailService->sendResetPasswordCompletedMail($userResetPassword->getUser());

        return $this->infoResponse(Message::RESET_PASSWORD_OK);
    }
}
