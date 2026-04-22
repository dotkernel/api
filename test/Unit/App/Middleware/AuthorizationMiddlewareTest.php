<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Middleware;

use Api\App\Middleware\AuthorizationMiddleware;
use Core\Admin\Entity\Admin;
use Core\Admin\Entity\AdminRole;
use Core\Admin\Enum\AdminRoleEnum;
use Core\Admin\Enum\AdminStatusEnum;
use Core\Admin\Repository\AdminRepository;
use Core\App\Message;
use Core\User\Entity\User;
use Core\User\Entity\UserRole;
use Core\User\Enum\UserRoleEnum;
use Core\User\Enum\UserStatusEnum;
use Core\User\Repository\UserRepository;
use Core\User\UserIdentity;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function json_decode;

class AuthorizationMiddlewareTest extends TestCase
{
    private AuthorizationMiddleware $authorizationMiddleware;
    private UserRepository&Stub $userRepository;
    private AdminRepository&Stub $adminRepository;
    private AuthorizationInterface&Stub $authorization;
    private ServerRequestInterface $request;
    private RequestHandlerInterface&Stub $handler;
    private ResponseInterface&Stub $response;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->userRepository  = $this->createStub(UserRepository::class);
        $this->adminRepository = $this->createStub(AdminRepository::class);
        $this->authorization   = $this->createStub(AuthorizationInterface::class);
        $this->handler         = $this->createStub(RequestHandlerInterface::class);
        $this->response        = $this->createStub(ResponseInterface::class);
        $this->request         = new ServerRequest();

        $this->authorizationMiddleware = new AuthorizationMiddleware(
            $this->authorization,
            $this->userRepository,
            $this->adminRepository
        );
    }

    public function testAuthorizationInvalidClientIdProvided(): void
    {
        $identity      = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'invalid_client_id']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $response = $this->authorizationMiddleware->process($this->request, $this->handler);
        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::INVALID_CLIENT_ID, $data['error']['messages']);
    }

    public function testAuthorizationInactiveAdmin(): void
    {
        $user = (new Admin())
            ->setIdentity('admin@dotkernel.com')
            ->setStatus(AdminStatusEnum::Inactive)
            ->addRole((new AdminRole())->setName(AdminRoleEnum::Admin));
        $this->adminRepository->method('findOneBy')->willReturn($user);

        $identity      = new UserIdentity('admin@dotkernel.com', ['admin'], ['oauth_client_id' => 'admin']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $response = $this->authorizationMiddleware->process($this->request, $this->handler);
        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::ADMIN_INACTIVE, $data['error']['messages']);
    }

    public function testAuthorizationInactiveUser(): void
    {
        $this->userRepository->method('findOneBy')->willReturn(new User());

        $identity      = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $response = $this->authorizationMiddleware->process($this->request, $this->handler);
        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::USER_NOT_ACTIVATED, $data['error']['messages']);
    }

    public function testAuthorizationUserNotFoundOrDeleted(): void
    {
        $user = (new User())->setStatus(UserStatusEnum::Deleted);
        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->authorization->method('isGranted')->willReturn(false);

        $identity      = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $response = $this->authorizationMiddleware->process($this->request, $this->handler);
        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::USER_NOT_FOUND, $data['error']['messages']);
    }

    public function testAuthorizationNotGranted(): void
    {
        $user = (new User())
            ->setIdentity('test@dotkernel.com')
            ->activate()
            ->addRole((new UserRole())->setName(UserRoleEnum::User));
        $this->userRepository->method('findOneBy')->willReturn($user);

        $identity      = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $response = $this->authorizationMiddleware->process($this->request, $this->handler);
        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(
            Message::RESOURCE_NOT_ALLOWED,
            $data['error']['messages']
        );
    }

    /**
     * @throws Exception
     */
    public function testAuthorizationAccessGranted(): void
    {
        $user = (new User())
            ->setIdentity('test@dotkernel.com')
            ->activate()
            ->addRole((new UserRole())->setName(UserRoleEnum::User));
        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->authorization->method('isGranted')->willReturn(true);

        $identity      = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function (ServerRequestInterface $request) use ($identity) {
                $user = $request->getAttribute(UserInterface::class);
                $this->assertSame($identity->getIdentity(), $user->getIdentity());
                $this->assertSame($identity->getDetails(), $user->getDetails());
                $this->assertSame($identity->getRoles(), $user->getRoles());
                return $this->response;
            });

        $this->authorizationMiddleware->process($this->request, $handler);
    }
}
