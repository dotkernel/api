<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Avatar;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\UpdateAvatarInputFilter;
use Api\User\Service\UserAvatarServiceInterface;
use Api\User\Service\UserServiceInterface;
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

        $user       = $this->userService->findOneBy(['uuid' => $request->getAttribute('uuid')]);
        $userAvatar = $this->userAvatarService->createAvatar($user, $this->inputFilter->getValue('avatar'));

        return $this->createdResponse($request, $userAvatar);
    }
}
