<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\CreateUserInputFilter;
use Api\User\InputFilter\UpdateUserInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class UserHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userService->findOneBy(['uuid' => $request->getAttribute('uuid')]);

        $this->userService->deleteUser($user);

        return $this->noContentResponse();
    }

    /**
     * @throws NotFoundException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userService->findOneBy(['uuid' => $request->getAttribute('uuid')]);

        return $this->createResponse($request, $user);
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateUserInputFilter())->setData((array) $request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        $user = $this->userService->findOneBy(['uuid' => $request->getAttribute('uuid')]);
        $this->userService->updateUser($user, $inputFilter->getValues());

        return $this->createResponse($request, $user);
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws MailException
     * @throws NotFoundException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new CreateUserInputFilter())->setData((array) $request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        $user = $this->userService->createUser($inputFilter->getValues());
        if ($user->isPending()) {
            $this->userService->sendActivationMail($user);
        } elseif ($user->isActive()) {
            $this->userService->sendWelcomeMail($user);
        }

        return $this->createdResponse($request, $user);
    }
}
