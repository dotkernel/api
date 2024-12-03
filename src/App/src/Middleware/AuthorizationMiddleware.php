<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\Admin\Entity\Admin;
use Api\Admin\Repository\AdminRepository;
use Api\App\Entity\Guest;
use Api\App\Entity\RoleInterface;
use Api\App\Message;
use Api\App\UserIdentity;
use Api\User\Entity\User;
use Api\User\Repository\UserRepository;
use Dot\DependencyInjection\Attribute\Inject;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;
use function sprintf;

class AuthorizationMiddleware implements MiddlewareInterface
{
    #[Inject(
        AuthorizationInterface::class,
        UserRepository::class,
        AdminRepository::class,
    )]
    public function __construct(
        protected AuthorizationInterface $authorization,
        protected UserRepository $userRepository,
        protected AdminRepository $adminRepository,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $defaultUser = $request->getAttribute(UserInterface::class);
        assert($defaultUser instanceof UserIdentity);

        switch ($defaultUser->getDetail('oauth_client_id')) {
            case 'admin':
                $user = $this->adminRepository->findOneBy(['identity' => $defaultUser->getIdentity()]);
                if (! $user instanceof Admin) {
                    return $this->unauthorizedResponse(sprintf(
                        Message::USER_NOT_FOUND_BY_IDENTITY,
                        $defaultUser->getIdentity()
                    ));
                }
                if (! $user->isActive()) {
                    return $this->unauthorizedResponse(Message::ADMIN_NOT_ACTIVATED);
                }
                $request = $request->withAttribute(Admin::class, $user);
                break;
            case 'frontend':
                $user = $this->userRepository->findOneBy(['identity' => $defaultUser->getIdentity()]);
                if (! $user instanceof User || $user->isDeleted()) {
                    return $this->unauthorizedResponse(sprintf(
                        Message::USER_NOT_FOUND_BY_IDENTITY,
                        $defaultUser->getIdentity()
                    ));
                }
                if (! $user->isActive()) {
                    return $this->unauthorizedResponse(Message::USER_NOT_ACTIVATED);
                }
                $request = $request->withAttribute(User::class, $user);
                break;
            case 'guest':
                $user    = new Guest();
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

        if (! $isGranted) {
            return $this->unauthorizedResponse(Message::RESOURCE_NOT_ALLOWED);
        }

        return $handler->handle($request);
    }

    protected function unauthorizedResponse(string $message): ResponseInterface
    {
        return new JsonResponse([
            'error' => [
                'messages' => [
                    $message,
                ],
            ],
        ], StatusCodeInterface::STATUS_FORBIDDEN);
    }
}
