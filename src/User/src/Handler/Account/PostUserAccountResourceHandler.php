<?php

declare(strict_types=1);

namespace Api\User\Handler\Account;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\App\Template\RendererInterface;
use Api\User\InputFilter\CreateUserInputFilter;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Core\App\Service\MailService;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostUserAccountResourceHandler extends AbstractHandler
{
    #[Inject(
        MailService::class,
        UserServiceInterface::class,
        CreateUserInputFilter::class,
        RendererInterface::class,
    )]
    public function __construct(
        protected MailService $mailService,
        protected UserServiceInterface $userService,
        protected CreateUserInputFilter $inputFilter,
        protected RendererInterface $renderer,
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
        $this->inputFilter
            ->setValidationGroup(['identity', 'password', 'passwordConfirm', 'detail'])
            ->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw BadRequestException::create(
                detail: Message::VALIDATOR_INVALID_DATA,
                additional: ['errors' => $this->inputFilter->getMessages()]
            );
        }

        $user = $this->userService->saveUser((array) $this->inputFilter->getValues());
        $this->mailService->sendActivationMail(
            $user,
            $this->renderer->render('user::activate', ['user' => $user])
        );

        return $this->createdResponse($request, $user);
    }
}
