<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Core\User\Entity\UserRole;
use Core\User\UserIdentity;
use Dot\DependencyInjection\Attribute\Inject;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware extends \Mezzio\Authentication\AuthenticationMiddleware
{
    #[Inject(
        AuthenticationInterface::class,
    )]
    public function __construct(
        AuthenticationInterface $auth,
    ) {
        parent::__construct($auth);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->auth->authenticate($request);
        if (! $user instanceof UserIdentity) {
            $user = new UserIdentity('guest', [
                UserRole::ROLE_GUEST,
            ], [
                'oauth_client_id' => 'guest',
            ]);
        }

        return $handler->handle(
            $request->withAttribute(UserInterface::class, $user)
        );
    }
}
