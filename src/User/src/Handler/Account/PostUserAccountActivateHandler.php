<?php

declare(strict_types=1);

namespace Api\User\Handler\Account;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\ActivateAccountInputFilter;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

class PostUserAccountActivateHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
        ActivateAccountInputFilter::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
        protected ActivateAccountInputFilter $inputFilter,
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
        $this->inputFilter->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($this->inputFilter->getMessages());
        }

        $user = $this->userService->findByEmail($this->inputFilter->getValue('email'));
        if ($user->isActive()) {
            throw new ConflictException(Message::USER_ALREADY_ACTIVATED);
        }

        $this->userService->activateUser($user);
        $this->userService->sendActivationMail($user);

        return $this->infoResponse(
            sprintf(Message::MAIL_SENT_USER_ACTIVATION, $user->getDetail()->getEmail()),
            StatusCodeInterface::STATUS_CREATED
        );
    }
}
