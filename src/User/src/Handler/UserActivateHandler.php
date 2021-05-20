<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Message;
use Api\App\Handler\DefaultHandler;
use Api\User\Entity\User;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function sprintf;

/**
 * Class UserActivateHandler
 * @package Api\User\Handler
 */
class UserActivateHandler extends DefaultHandler
{
    protected UserService $userService;

    /**
     * UserActivateHandler constructor.
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
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            $user = $this->userService->findOneBy(['uuid' => $uuid]);
            if (!($user instanceof User)) {
                return $this->notFoundResponse(
                    sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid)
                );
            }

            if ($user->getStatus() === User::STATUS_ACTIVE) {
                return $this->errorResponse(Message::USER_ALREADY_ACTIVATED);
            }

            $user = $this->userService->updateUser($user->renewHash());
            $this->userService->sendActivationMail($user);
            return $this->infoResponse(Message::USER_ACTIVATED);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
