<?php

declare(strict_types=1);

namespace Api\User\Middleware;

use Api\User\Entity\UserEntity;
use Api\User\Entity\UserIdentity;
use Api\User\Entity\UserRoleEntity;
use Api\User\Service\UserService;
use Api\App\Common\Message;
use Dot\AnnotatedServices\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Laminas\Http\Response;
use Exception;

use function array_map;

/**
 * Class AuthMiddleware
 * @package Api\User\Middleware
 */
class AuthMiddleware implements MiddlewareInterface
{
    /** @var UserService $userService */
    protected $userService;

    /** @var AuthorizationInterface $authorization */
    protected $authorization;

    /**
     * AuthMiddleware constructor.
     * @param UserService $userService
     * @param AuthorizationInterface $authorization
     *
     * @Inject({UserService::class, AuthorizationInterface::class})
     */
    public function __construct(UserService $userService, AuthorizationInterface $authorization)
    {
        $this->userService = $userService;
        $this->authorization = $authorization;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var UserIdentity $defaultUser */
        $defaultUser = $request->getAttribute(UserInterface::class);

        $user = $this->userService->findOneBy(['email' => $defaultUser->getIdentity(), 'isDeleted' => false]);
        if ($user->getStatus() !== UserEntity::STATUS_ACTIVE) {
            return new JsonResponse([
                'error' => [
                    'messages' => [
                        Message::USER_NOT_ACTIVATED
                    ]
                ]
            ], Response::STATUS_CODE_403);
        }

        $defaultUser->setRoles(array_map(function (UserRoleEntity $role) {
            return $role->getName();
        }, $user->getRoles()->getIterator()->getArrayCopy()));

        $request = $request->withAttribute(UserEntity::class, $user);
        $request = $request->withAttribute(UserInterface::class, $defaultUser);

        $isGranted = false;
        foreach ($defaultUser->getRoles() as $role) {
            if ($this->authorization->isGranted($role, $request)) {
                $isGranted = true;
                break;
            }
        }

        if (!$isGranted) {
            return new JsonResponse([
                'error' => [
                    'messages' => [
                        Message::RESOURCE_NOT_ALLOWED
                    ]
                ]
            ], Response::STATUS_CODE_403);
        }

        return $handler->handle($request);
    }
}
