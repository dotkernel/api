<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\CreateUserInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostUserResourceHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
        CreateUserInputFilter::class,
    )]
    public function __construct(
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
            $this->userService->sendActivationMail($user);
        } elseif ($user->isActive()) {
            $this->userService->sendWelcomeMail($user);
        }

        return $this->createdResponse($request, $user);
    }
}
