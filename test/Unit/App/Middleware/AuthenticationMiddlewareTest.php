<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Middleware;

use Api\App\Middleware\AuthenticationMiddleware as Subject;
use Api\User\Entity\UserRole;
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
    private Subject $subject;
    private AuthenticationInterface|MockObject $auth;
    private ServerRequestInterface $request;
    private RequestHandlerInterface|MockObject $handler;
    private ResponseInterface|MockObject $response;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->auth     = $this->createMock(AuthenticationInterface::class);
        $this->handler  = $this->createMock(RequestHandlerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->request  = new ServerRequest();
        $this->subject  = new Subject($this->auth);
    }

    public function testAuthenticationFailsFallbackToGuestUser(): void
    {
        $this->auth->method('authenticate')->willReturn(null);

        $this->handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function (ServerRequestInterface $request) {
                $user = $request->getAttribute(UserInterface::class);
                $this->assertInstanceOf(UserInterface::class, $user);
                $this->assertSame(UserRole::ROLE_GUEST, $user->getIdentity());
                $this->assertSame(['guest'], $user->getRoles());
                $this->assertCount(1, $user->getRoles());
                return $this->response;
            }));

        $this->subject->process($this->request, $this->handler);
    }
}
