<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Handler\ResponseTrait;
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
use Throwable;

use function sprintf;

class UserAvatarHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     UserServiceInterface::class,
     *     UserAvatarServiceInterface::class
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserServiceInterface $userService,
        protected UserAvatarServiceInterface $userAvatarService
    ) {
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            $user = $this->userService->findOneBy(['uuid' => $uuid]);
            if (! $user->hasAvatar()) {
                return $this->notFoundResponse(Message::AVATAR_MISSING);
            }

            $this->userAvatarService->removeAvatar($user);

            return $this->infoResponse(Message::AVATAR_DELETED);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');
        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (! $user->hasAvatar()) {
            return $this->notFoundResponse(Message::AVATAR_MISSING);
        }

        return $this->createResponse($request, $user->getAvatar());
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateAvatarInputFilter())->setData($request->getUploadedFiles());
        if (! $inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $uuid = $request->getAttribute('uuid');
            $user = $this->userService->findOneBy(['uuid' => $uuid]);
            if (! $user instanceof User) {
                return $this->notFoundResponse(
                    sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid)
                );
            }

            $userAvatar = $this->userAvatarService->createAvatar($user, $inputFilter->getValue('avatar'));

            return $this->createResponse($request, $userAvatar);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
