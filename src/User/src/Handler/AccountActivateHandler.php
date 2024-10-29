<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\App\Message;
use Api\User\InputFilter\ActivateAccountInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

class AccountActivateHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
    ) {
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userService->findOneBy(['hash' => $request->getAttribute('hash')]);
        if ($user->isActive()) {
            throw new ConflictException(Message::USER_ALREADY_ACTIVATED);
        }

        $this->userService->activateUser($user);

        return $this->infoResponse(Message::USER_ACTIVATED);
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws MailException
     * @throws NotFoundException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new ActivateAccountInputFilter())->setData((array) $request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        $user = $this->userService->findByEmail($inputFilter->getValue('email'));
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
