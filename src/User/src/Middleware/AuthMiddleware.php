<?php

declare(strict_types=1);

namespace Api\User\Middleware;

use Api\User\Entity\Admin;
use Dot\AnnotatedServices\Annotation\Inject;
use Api\User\Entity\User;
use Api\User\Entity\UserIdentity;
use Api\User\Entity\UserRole;
use Api\User\Service\AdminService;
use Api\User\Service\UserService;
use Api\App\Common\Message;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Http\Response;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
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

    /** @var AdminService $adminService */
    protected $adminService;

    /**
     * AuthMiddleware constructor.
     * @param UserService $userService
     * @param AuthorizationInterface $authorization
     * @param AdminService $adminService
     *
     * @Inject({UserService::class, AuthorizationInterface::class, AdminService::class})
     */
    public function __construct(
        UserService $userService,
        AuthorizationInterface $authorization,
        AdminService $adminService
    ) {
        $this->userService = $userService;
        $this->authorization = $authorization;
        $this->adminService = $adminService;
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
        switch ($defaultUser->getDetail('oauth_client_id')) {
            case 'frontend':
                $user = $this->userService->findOneBy(
                    [
                        'identity' => $defaultUser->getIdentity(),
                        'isDeleted' => false
                    ]
                );
                if ($user->getStatus() !== User::STATUS_ACTIVE) {
                    return new JsonResponse([
                        'error' => [
                            'messages' => [
                                Message::USER_NOT_ACTIVATED
                            ]
                        ]
                    ], Response::STATUS_CODE_403);
                }
                $request = $request->withAttribute(User::class, $user);
                break;
            case 'admin':
                $user = $this->adminService->findOneBy(
                    [
                        'identity' => $defaultUser->getIdentity(),
                    ]
                );
                if ($user->getStatus() !== Admin::STATUS_ACTIVE) {
                    return new JsonResponse([
                        'error' => [
                            'messages' => [
                                Message::ADMIN_NOT_ACTIVATED
                            ]
                        ]
                    ], Response::STATUS_CODE_403);
                }
                $request = $request->withAttribute(Admin::class, $user);
                break;
            default:
                return new JsonResponse([
                    'error' => [
                        'messages' => [
                            Message::INVALID_CLIENT_ID
                        ]
                    ]
                ], Response::STATUS_CODE_403);
        }

        $defaultUser->setRoles(array_map(function ($role) {
            return $role->getName();
        }, $user->getRoles()->getIterator()->getArrayCopy()));

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
