<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Handler\ResponseTrait;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\Service\UserServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function sprintf;

class UserActivateHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     UserServiceInterface::class
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserServiceInterface $userService
    ) {
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            $user = $this->userService->findOneBy(['uuid' => $uuid]);
            if (! $user instanceof User) {
                return $this->notFoundResponse(
                    sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid)
                );
            }

            if ($user->isActive()) {
                return $this->errorResponse(Message::USER_ALREADY_ACTIVATED);
            }

            $user = $this->userService->activateUser($user);

            $this->userService->sendActivationMail($user);

            return $this->infoResponse(Message::USER_ACTIVATED);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
