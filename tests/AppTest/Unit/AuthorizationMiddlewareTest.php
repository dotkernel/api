<?php

namespace AppTest\Unit;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Repository\AdminRepository;
use Api\App\Message;
use Api\App\Middleware\AuthorizationMiddleware as Subject;
use Api\App\UserIdentity;
use Api\User\Entity\User;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserRepository;
use Laminas\Diactoros\ServerRequest;
use Laminas\Http\Response;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authorization\AuthorizationInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AuthorizationMiddlewareTest
 * @package Test\Unit
 */
class AuthorizationMiddlewareTest extends TestCase
{
    private Subject $subject;

    private UserRepository $userRepository;

    private AdminRepository $adminRepository;

    private AuthorizationInterface $authorization;

    private ServerRequestInterface $request;

    private RequestHandlerInterface $handler;

    private ResponseInterface $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->adminRepository = $this->createMock(AdminRepository::class);
        $this->authorization = $this->createMock(AuthorizationInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->request = new ServerRequest();

        $this->subject = new Subject($this->authorization, $this->userRepository, $this->adminRepository);
    }

    public function testAuthorizationInvalidClientIdProvided()
    {
        $identity = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'invalid_client_id']);

        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $response = $this->subject->process($this->request, $this->handler);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(Response::STATUS_CODE_403, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::INVALID_CLIENT_ID, $data['error']['messages']);
    }

    public function testAuthorizationInactiveAdmin()
    {
        $identity = new UserIdentity('admin@dotkernel.com', ['admin'], ['oauth_client_id' => 'admin']);

        $user = (new Admin())
            ->setIdentity('admin@dotkernel.com')
            ->setStatus(Admin::STATUS_INACTIVE)
            ->addRole((new AdminRole())->setName(AdminRole::ROLE_ADMIN));

        $this->adminRepository->method('findOneBy')->willReturn($user);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $response = $this->subject->process($this->request, $this->handler);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(Response::STATUS_CODE_403, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::ADMIN_NOT_ACTIVATED, $data['error']['messages']);
    }

    public function testAuthorizationInactiveUser()
    {
        $identity = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);
        $user = (new User());

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $response = $this->subject->process($this->request, $this->handler);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(Response::STATUS_CODE_403, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(Message::USER_NOT_ACTIVATED, $data['error']['messages']);
    }

    public function testAuthorizationUserNotFoundOrDeleted()
    {
        $identity = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);
        $user = (new User())->markAsDeleted();

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);
        $this->authorization->method('isGranted')->willReturn(false);

        $response = $this->subject->process($this->request, $this->handler);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(Response::STATUS_CODE_403, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(
            sprintf(Message::USER_NOT_FOUND_BY_IDENTITY, $identity->getIdentity()),
            $data['error']['messages']
        );
    }

    public function testAuthorizationNotGranted()
    {
        $identity = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);

        $user = (new User())
            ->setIdentity('test@dotkernel.com')
            ->activate()
            ->addRole((new UserRole())->setName(UserRole::ROLE_USER));

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);

        $response = $this->subject->process($this->request, $this->handler);
        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertSame(Response::STATUS_CODE_403, $response->getStatusCode());
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('messages', $data['error']);
        $this->assertContains(
            Message::RESOURCE_NOT_ALLOWED,
            $data['error']['messages']
        );
    }

    public function testAuthorizationAccessGranted()
    {
        $identity = new UserIdentity('test@dotkernel.com', ['user'], ['oauth_client_id' => 'frontend']);

        $user = (new User())
            ->setIdentity('test@dotkernel.com')
            ->activate()
            ->addRole((new UserRole())->setName(UserRole::ROLE_USER));

        $this->userRepository->method('findOneBy')->willReturn($user);
        $this->request = $this->request->withAttribute(UserInterface::class, $identity);
        $this->authorization->method('isGranted')->willReturn(true);

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->will($this->returnCallback(function(ServerRequestInterface $request) use($identity) {
                $user = $request->getAttribute(UserInterface::class);
                $this->assertSame($identity->getIdentity(), $user->getIdentity());
                $this->assertSame($identity->getDetails(), $user->getDetails());
                $this->assertSame($identity->getRoles(), $user->getRoles());
                return $this->response;
            }));

        $this->subject->process($this->request, $this->handler);
    }
}
