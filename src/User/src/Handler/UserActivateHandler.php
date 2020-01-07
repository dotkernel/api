<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Common\Message;
use Api\App\RestDispatchTrait;
use Api\User\Entity\UserEntity;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;

use function sprintf;

/**
 * Class UserActivateHandler
 * @package Api\User\Handler
 */
class UserActivateHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var UserService $userService */
    protected $userService;

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
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request) : ResponseInterface
    {
        $uuid = $request->getAttribute('uuid', null);
        if (empty($uuid)) {
            return $this->errorResponse(sprintf(Message::MISSING_PARAMETER, 'uuid'));
        }

        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (!($user instanceof UserEntity)) {
            return $this->notFoundResponse(
                sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid)
            );
        }

        if ($user->getStatus() === UserEntity::STATUS_ACTIVE) {
            return $this->errorResponse(Message::USER_ALREADY_ACTIVATED);
        }

        try {
            $user = $this->userService->updateUser($user->renewHash());
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        try {
            $this->userService->sendActivationMail($user);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse($request, $this->resourceGenerator->fromObject($user, $request));
    }
}
