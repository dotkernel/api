<?php

declare(strict_types=1);

namespace Api\User\Handler\Account;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\RecoverIdentityInputFilter;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostUserAccountRecoverHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
        RecoverIdentityInputFilter::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
        protected RecoverIdentityInputFilter $inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
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
        $this->userService->sendRecoverIdentityMail($user);

        return $this->infoResponse(Message::MAIL_SENT_RECOVER_IDENTITY);
    }
}
