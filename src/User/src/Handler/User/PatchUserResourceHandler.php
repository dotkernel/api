<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\UpdateUserInputFilter;
use Api\User\Service\UserServiceInterface;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchUserResourceHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
        UpdateUserInputFilter::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
        protected UpdateUserInputFilter $inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw BadRequestException::create(
                detail: Message::VALIDATOR_INVALID_DATA,
                additional: ['errors' => $this->inputFilter->getMessages()]
            );
        }

        return $this->createResponse(
            $request,
            $this->userService->saveUser(
                (array) $this->inputFilter->getValues(),
                $this->userService->findUser($request->getAttribute('uuid'))
            )
        );
    }
}
