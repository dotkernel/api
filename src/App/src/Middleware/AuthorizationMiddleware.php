<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\Admin\Entity\Admin;
use Api\Admin\Service\AdminService;
use Dot\AnnotatedServices\Annotation\Inject;
use Api\App\Entity\Guest;
use Api\App\Entity\RoleInterface;
use Api\App\Message;
use Api\App\UserIdentity;
use Api\User\Entity\User;
use Api\User\Service\UserService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Throwable;

/**
 * Class AuthorizationMiddleware
 * @package Api\App\Middleware
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    protected UserService $userService;

    protected AuthorizationInterface $authorization;

    protected AdminService $adminService;

    /**
     * AuthorizationMiddleware constructor.
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
     * @throws Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        /** @var UserIdentity $defaultUser */
        $defaultUser = $request->getAttribute(UserInterface::class);
        switch ($defaultUser->getDetail('oauth_client_id')) {
            case 'admin':
                $user = $this->adminService->findByIdentity($defaultUser->getIdentity());
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
            case 'frontend':
                $user = $this->userService->findByIdentity($defaultUser->getIdentity());
                if (!($user instanceof User) || $user->isDeleted()) {
                    return new JsonResponse([
                        'error' => [
                            'messages' => [
                                sprintf(Message::USER_NOT_FOUND_BY_IDENTITY, $defaultUser->getIdentity())
                            ]
                        ]
                    ], Response::STATUS_CODE_403);
                }
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
            case 'guest':
                $user = new Guest();
                $request = $request->withAttribute(Guest::class, $user);
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

        $defaultUser->setRoles($user->getRoles()->map(function (RoleInterface $role) {
            return $role->getName();
        })->toArray());

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
