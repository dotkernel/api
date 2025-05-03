<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Core\User\Enum\UserRoleEnum;
use Core\User\UserIdentity;
use Dot\DependencyInjection\Attribute\Inject;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    #[Inject(
        AuthenticationInterface::class,
    )]
    public function __construct(
        protected AuthenticationInterface $auth,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->auth->authenticate($request);
        if (! $user instanceof UserIdentity) {
            $user = new UserIdentity('guest', [
                UserRoleEnum::Guest,
            ], [
                'oauth_client_id' => 'guest',
            ]);
        }

        return $handler->handle(
            $request->withAttribute(UserInterface::class, $user)
        );
    }
}
