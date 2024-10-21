<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Middleware;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Repository\AdminRepository;
use Api\App\Exception\ForbiddenException;
use Api\App\Exception\UnauthorizedException;
use Api\App\Middleware\AuthorizationMiddleware as Subject;
use Api\App\UserIdentity;
use Api\User\Entity\User;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserRepository;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthorizationMiddlewareTest extends TestCase
{
    private Subject $subject;
    private UserRepository $userRepository;
    private AdminRepository $adminRepository;
    private AuthorizationInterface $authorization;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private ResponseInterface $response;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->userRepository  = $this->createMock(UserRepository::class);
        $this->adminRepository = $this->createMock(AdminRepository::class);
        $this->authorization   = $this->createMock(AuthorizationInterface::class);
        $this->handler         = $this->createMock(RequestHandlerInterface::class);
        $this->response        = $this->createMock(ResponseInterface::class);
        $this->request         = new ServerRequest();
        $this->subject         = new Subject(
            $this->authorization,
            $this->userRepository,
            $this->adminRepository
        );
    }

    public function testAuthorizationInvalidClientIdProvided(): void
    {
        $identity      = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'invalid_client_id']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $this->expectException(UnauthorizedException::class);
        $this->subject->process($this->request, $this->handler);
    }

    public function testAuthorizationInactiveAdmin(): void
    {
        $user = (new Admin())
            ->setIdentity('admin@dotkernel.com')
            ->setStatus(Admin::STATUS_INACTIVE)
            ->addRole((new AdminRole())->setName(AdminRole::ROLE_ADMIN));
        $this->adminRepository->method('findOneBy')->willReturn($user);

        $identity      = new UserIdentity('admin@dotkernel.com', ['admin'], ['oauth_client_id' => 'admin']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $this->expectException(UnauthorizedException::class);
        $this->subject->process($this->request, $this->handler);
    }

    public function testAuthorizationInactiveUser(): void
    {
        $this->userRepository->method('findOneBy')->willReturn(new User());

        $identity      = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $this->expectException(UnauthorizedException::class);
        $this->subject->process($this->request, $this->handler);
    }

    public function testAuthorizationUserNotFoundOrDeleted(): void
    {
        $user = (new User())->markAsDeleted();
        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->authorization->method('isGranted')->willReturn(false);

        $identity      = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $this->expectException(UnauthorizedException::class);
        $this->subject->process($this->request, $this->handler);
    }

    public function testAuthorizationNotGranted(): void
    {
        $user = (new User())
            ->setIdentity('test@dotkernel.com')
            ->activate()
            ->addRole((new UserRole())->setName(UserRole::ROLE_USER));
        $this->userRepository->method('findOneBy')->willReturn($user);

        $identity      = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $this->expectException(ForbiddenException::class);
        $this->subject->process($this->request, $this->handler);
    }

    public function testAuthorizationAccessGranted(): void
    {
        $user = (new User())
            ->setIdentity('test@dotkernel.com')
            ->activate()
            ->addRole((new UserRole())->setName(UserRole::ROLE_USER));
        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->authorization->method('isGranted')->willReturn(true);

        $identity      = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function (ServerRequestInterface $request) use ($identity) {
                $user = $request->getAttribute(UserInterface::class);
                $this->assertSame($identity->getIdentity(), $user->getIdentity());
                $this->assertSame($identity->getDetails(), $user->getDetails());
                $this->assertSame($identity->getRoles(), $user->getRoles());
                return $this->response;
            }));

        $this->subject->process($this->request, $this->handler);
    }
}
