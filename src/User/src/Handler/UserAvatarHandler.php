<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\HandlerTrait;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\InputFilter\UpdateAvatarInputFilter;
use Api\User\Service\UserAvatarServiceInterface;
use Api\User\Service\UserServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function sprintf;

class UserAvatarHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     UserServiceInterface::class,
     *     UserAvatarServiceInterface::class,
     *     "config"
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserServiceInterface $userService,
        protected UserAvatarServiceInterface $userAvatarService,
        protected array $config,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');
        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (! $user instanceof User) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
        }
        if (! $user->hasAvatar()) {
            throw new NotFoundException(Message::AVATAR_MISSING);
        }

        $this->userAvatarService->removeAvatar($user);

        return $this->infoResponse(Message::AVATAR_DELETED);
    }

    /**
     * @throws NotFoundException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');
        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (! $user instanceof User) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
        }
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

        $uuid = $request->getAttribute('uuid');
        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (! $user instanceof User) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
        }

        $userAvatar = $this->userAvatarService->createAvatar($user, $inputFilter->getValue('avatar'));

        return $this->createdResponse($request, $userAvatar);
    }
}
