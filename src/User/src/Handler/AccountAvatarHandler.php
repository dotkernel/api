<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Message;
use Api\App\Handler\DefaultHandler;
use Api\User\Entity\User;
use Api\User\Entity\UserAvatar;
use Api\User\Form\InputFilter\UpdateAvatarInputFilter;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class AccountAvatarHandler
 * @package Api\User\Handler
 */
class AccountAvatarHandler extends DefaultHandler
{
    protected UserService $userService;

    /**
     * AccountAvatarHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param UserService $userService
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, UserService::class})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        UserService $userService
    ) {
        parent::__construct($halResponseFactory, $resourceGenerator);

        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            /** @var User $user */
            $user = $request->getAttribute(User::class);
            if (!($user->getAvatar() instanceof UserAvatar)) {
                return $this->notFoundResponse(Message::AVATAR_MISSING);
            }
            $this->userService->removeAvatar($user);
            return $this->infoResponse(Message::AVATAR_DELETED);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute(User::class);
        if (!($user->getAvatar() instanceof UserAvatar)) {
            return $this->notFoundResponse(Message::AVATAR_MISSING);
        }
        return $this->createResponse($request, $user->getAvatar());
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateAvatarInputFilter())->getInputFilter();
        $inputFilter->setData($request->getUploadedFiles());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $user = $this->userService->updateUser($request->getAttribute(User::class), $inputFilter->getValues());
            return $this->createResponse($request, $user->getAvatar());
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
