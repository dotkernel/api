<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\Admin\Entity\Admin;
use Api\Admin\Repository\AdminRepository;
use Api\App\Entity\Guest;
use Api\App\Entity\RoleInterface;
use Api\App\Exception\ForbiddenException;
use Api\App\Exception\UnauthorizedException;
use Api\App\Message;
use Api\App\UserIdentity;
use Api\User\Entity\User;
use Api\User\Repository\UserRepository;
use Dot\DependencyInjection\Attribute\Inject;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;

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

    /**
     * @throws ForbiddenException
     * @throws UnauthorizedException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $defaultUser = $request->getAttribute(UserInterface::class);
        assert($defaultUser instanceof UserIdentity);

        switch ($defaultUser->getDetail('oauth_client_id')) {
            case 'admin':
                $account = $this->adminRepository->findOneBy(['identity' => $defaultUser->getIdentity()]);
                if (! $account instanceof Admin) {
                    throw UnauthorizedException::create(Message::ADMIN_NOT_FOUND);
                }
                if (! $account->isActive()) {
                    throw UnauthorizedException::create(Message::ADMIN_INACTIVE);
                }
                $request = $request->withAttribute(Admin::class, $account);
                break;
            case 'frontend':
                $account = $this->userRepository->findOneBy(['identity' => $defaultUser->getIdentity()]);
                if (! $account instanceof User || $account->isDeleted()) {
                    throw UnauthorizedException::create(Message::USER_NOT_FOUND);
                }
                if ($account->getStatus() !== User::STATUS_ACTIVE) {
                    throw UnauthorizedException::create(Message::USER_INACTIVE);
                }
                $request = $request->withAttribute(User::class, $account);
                break;
            case 'guest':
                $account = new Guest();
                $request = $request->withAttribute(Guest::class, $account);
                break;
            default:
                throw UnauthorizedException::create(Message::INVALID_CLIENT_ID);
        }

        $defaultUser->setRoles($account->getRoles()->map(function (RoleInterface $role) {
            return $role->getName();
        })->toArray());

        $request = $request->withAttribute(UserInterface::class, $defaultUser);

        foreach ($defaultUser->getRoles() as $role) {
            if ($this->authorization->isGranted($role, $request)) {
                return $handler->handle($request);
            }
        }

        throw ForbiddenException::create(Message::NOT_ENOUGH_PERMISSIONS);
    }
}
