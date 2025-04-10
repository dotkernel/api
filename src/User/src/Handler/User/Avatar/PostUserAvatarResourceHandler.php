<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Avatar;

use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\UpdateAvatarInputFilter;
use Api\User\Service\UserAvatarServiceInterface;
use Api\User\Service\UserServiceInterface;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\NotFoundException;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostUserAvatarResourceHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
        UserAvatarServiceInterface::class,
        UpdateAvatarInputFilter::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
        protected UserAvatarServiceInterface $userAvatarService,
        protected UpdateAvatarInputFilter $inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setData($request->getUploadedFiles());
        if (! $this->inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($this->inputFilter->getMessages());
        }

        return $this->createdResponse(
            $request,
            $this->userAvatarService->saveAvatar(
                $this->userService->findUser($request->getAttribute('uuid')),
                $this->inputFilter->getValue('avatar')
            )
        );
    }
}
