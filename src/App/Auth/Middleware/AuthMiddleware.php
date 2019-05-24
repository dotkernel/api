<?php

declare(strict_types=1);

namespace App\Auth\Middleware;

use App\User\Entity\UserEntity;
use App\User\Entity\UserRoleEntity;
use App\User\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Expressive\Authorization\AuthorizationInterface;
use Zend\Http\Response;

/**
 * Class IdentityMiddleware
 * @package App\User\Middleware
 */
class AuthMiddleware implements MiddlewareInterface
{
    /** @var UserService $userService */
    protected $userService;

    /** @var AuthorizationInterface $authorization */
    protected $authorization;

    /**
     * IdentityMiddleware constructor.
     * @param UserService $userService
     * @param AuthorizationInterface $authorization
     */
    public function __construct(UserService $userService, AuthorizationInterface $authorization)
    {
        $this->userService = $userService;
        $this->authorization = $authorization;
    }

    /**
     * @return UserService
     */
    public function getUserService(): UserService
    {
        return $this->userService;
    }

    /**
     * @return AuthorizationInterface
     */
    public function getAuthorization(): AuthorizationInterface
    {
        return $this->authorization;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var UserInterface $defaultUser */
        $defaultUser = $request->getAttribute(UserInterface::class);

        $user = $this->getUserService()->identify($defaultUser->getIdentity());

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
                        'You are not allowed to access this resource.'
                    ]
                ]
            ], Response::STATUS_CODE_403);
        }

        return $handler->handle($request);
    }
}
