<?php

namespace Unit;

use Api\App\Middleware\AuthenticationMiddleware as Subject;
use Api\User\Entity\UserRole;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AuthenticationMiddlewareTest
 * @package Unit
 */
class AuthenticationMiddlewareTest extends TestCase
{
    private Subject $subject;

    private AuthenticationInterface $auth;

    private ServerRequestInterface $request;

    private RequestHandlerInterface $handler;

    private ResponseInterface $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->auth = $this->createMock(AuthenticationInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->request = new ServerRequest();

        $this->subject = new Subject($this->auth);
    }

    public function testAuthenticationFailsFallbackToGuestUser()
    {
        $this->auth->method('authenticate')->willReturn(null);

        $this->handler->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function(ServerRequestInterface $request) {
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
