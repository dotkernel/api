<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Middleware;

use Api\App\Middleware\AuthenticationMiddleware;
use Core\User\Enum\UserRoleEnum;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddlewareTest extends TestCase
{
    private AuthenticationMiddleware $authenticationMiddleware;
    private AuthenticationInterface&MockObject $auth;
    private ServerRequestInterface $request;
    private RequestHandlerInterface&MockObject $handler;
    private ResponseInterface&MockObject $response;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->auth     = $this->createMock(AuthenticationInterface::class);
        $this->handler  = $this->createMock(RequestHandlerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->request  = new ServerRequest();

        $this->authenticationMiddleware = new AuthenticationMiddleware($this->auth);
    }

    public function testAuthenticationFailsFallbackToGuestUser(): void
    {
        $this->auth->method('authenticate')->willReturn(null);

        $this->handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (ServerRequestInterface $request) {
                $user = $request->getAttribute(UserInterface::class);
                $this->assertInstanceOf(UserInterface::class, $user);
                $this->assertSame(UserRoleEnum::Guest->value, $user->getIdentity());
                $this->assertSame([UserRoleEnum::Guest], $user->getRoles());
                $this->assertCount(1, $user->getRoles());
                return $this->response;
            });

        $this->authenticationMiddleware->process($this->request, $this->handler);
    }
}
