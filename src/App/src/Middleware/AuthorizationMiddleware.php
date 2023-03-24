<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\Admin\Entity\Admin;
use Api\Admin\Repository\AdminRepository;
use Api\User\Repository\UserRepository;
use Dot\AnnotatedServices\Annotation\Inject;
use Api\App\Entity\Guest;
use Api\App\Entity\RoleInterface;
use Api\App\Message;
use Api\App\UserIdentity;
use Api\User\Entity\User;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;

class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @Inject({
     *     AuthorizationInterface::class,
     *     UserRepository::class,
     *     AdminRepository::class
     * })
     */
    public function __construct(
        protected AuthorizationInterface $authorization,
        protected UserRepository $userRepository,
        protected AdminRepository $adminRepository
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var UserIdentity $defaultUser */
        $defaultUser = $request->getAttribute(UserInterface::class);
        switch ($defaultUser->getDetail('oauth_client_id')) {
            case 'admin':
                $user = $this->adminRepository->findOneBy(['identity' => $defaultUser->getIdentity()]);
                if (!$user instanceof Admin) {
                    return $this->unauthorizedResponse(sprintf(
                        Message::USER_NOT_FOUND_BY_IDENTITY,
                        $defaultUser->getIdentity()
                    ));
                }
                if (!$user->isActive()) {
                    return $this->unauthorizedResponse(Message::ADMIN_NOT_ACTIVATED);
                }
                $request = $request->withAttribute(Admin::class, $user);
                break;
            case 'frontend':
                $user = $this->userRepository->findOneBy(['identity' => $defaultUser->getIdentity()]);
                if (!$user instanceof User || $user->isDeleted()) {
                    return $this->unauthorizedResponse(sprintf(
                        Message::USER_NOT_FOUND_BY_IDENTITY,
                        $defaultUser->getIdentity()
                    ));
                }
                if ($user->getStatus() !== User::STATUS_ACTIVE) {
                    return $this->unauthorizedResponse(Message::USER_NOT_ACTIVATED);
                }
                $request = $request->withAttribute(User::class, $user);
                break;
            case 'guest':
                $user = new Guest();
                $request = $request->withAttribute(Guest::class, $user);
                break;
            default:
                return $this->unauthorizedResponse(Message::INVALID_CLIENT_ID);
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
            return $this->unauthorizedResponse(Message::RESOURCE_NOT_ALLOWED);
        }

        return $handler->handle($request);
    }

    protected function unauthorizedResponse(string $message): ResponseInterface
    {
        return new JsonResponse([
            'error' => [
                'messages' => [
                    $message
                ]
            ]
        ], Response::STATUS_CODE_403);
    }
}
