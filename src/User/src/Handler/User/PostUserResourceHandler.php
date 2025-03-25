<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\CreateUserInputFilter;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
use Core\App\Service\MailService;
use Core\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostUserResourceHandler extends AbstractHandler
{
    #[Inject(
        MailService::class,
        UserServiceInterface::class,
        CreateUserInputFilter::class,
    )]
    public function __construct(
        protected MailService $mailService,
        protected UserServiceInterface $userService,
        protected CreateUserInputFilter $inputFilter,
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

        $user = $this->userService->createUser((array) $this->inputFilter->getValues());
        if ($user->isPending()) {
            $this->mailService->sendActivationMail($user);
        } elseif ($user->isActive()) {
            $this->mailService->sendWelcomeMail($user);
        }

        return $this->createdResponse($request, $user);
    }
}
