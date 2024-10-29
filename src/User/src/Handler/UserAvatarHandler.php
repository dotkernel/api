<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\App\Message;
use Api\User\InputFilter\UpdateAvatarInputFilter;
use Api\User\Service\UserAvatarServiceInterface;
use Api\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserAvatarHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
        UserAvatarServiceInterface::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
        protected UserAvatarServiceInterface $userAvatarService,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userService->findOneBy(['uuid' => $request->getAttribute('uuid')]);
        if (! $user->hasAvatar()) {
            throw new NotFoundException(Message::AVATAR_MISSING);
        }

        $this->userAvatarService->removeAvatar($user);

        return $this->noContentResponse();
    }

    /**
     * @throws NotFoundException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->userService->findOneBy(['uuid' => $request->getAttribute('uuid')]);
        if (! $user->hasAvatar()) {
            throw new NotFoundException(Message::AVATAR_MISSING);
        }

        return $this->createResponse($request, $user->getAvatar());
    }

    /**
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateAvatarInputFilter())->setData($request->getUploadedFiles());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        $user = $this->userService->findOneBy(['uuid' => $request->getAttribute('uuid')]);

        $userAvatar = $this->userAvatarService->createAvatar($user, $inputFilter->getValue('avatar'));

        return $this->createdResponse($request, $userAvatar);
    }
}
