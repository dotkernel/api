<?php

declare(strict_types=1);

namespace ApiTest\Functional;

use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\App\Entity\RoleInterface;
use Api\User\Entity\User;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserRole;
use ApiTest\Functional\Traits\AuthenticationTrait;
use ApiTest\Functional\Traits\DatabaseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

use function array_merge;
use function getenv;
use function method_exists;
use function putenv;
use function realpath;

class AbstractFunctionalTest extends TestCase
{
    use AuthenticationTrait;
    use DatabaseTrait;

    protected Application $app;
    protected ContainerInterface|ServiceManager $container;
    protected const DEFAULT_PASSWORD = 'dotkernel';

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function setUp(): void
    {
        $this->enableTestMode();

        $this->initContainer();
        $this->initApp();
        $this->initPipeline();
        $this->initRoutes();

        if ($this->isTestMode() && ! $this->getEntityManager()->getConnection()->getParams()['memory']) {
            throw new RuntimeException(
                'You are running tests in a non in-memory database.
                Did you forgot to create the local.test.php file?'
            );
        }
        if (method_exists($this, 'runMigrations')) {
            $this->runMigrations();
        }
        if (method_exists($this, 'runSeeders')) {
            $this->runSeeders();
        }
    }

    public function tearDown(): void
    {
        $this->disableTestMode();

        parent::tearDown();
    }

    private function enableTestMode(): void
    {
        putenv('TEST_MODE=true');
    }

    private function disableTestMode(): void
    {
        putenv('TEST_MODE');
    }

    public static function isTestMode(): bool
    {
        return getenv('TEST_MODE') === 'true';
    }

    private function initContainer(): void
    {
        $this->container = require realpath(__DIR__ . '/../../config/container.php');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function initApp(): void
    {
        $this->app = $this->container->get(Application::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function initPipeline(): void
    {
        $factory = $this->container->get(MiddlewareFactory::class);
        (require realpath(__DIR__ . '/../../config/pipeline.php'))($this->app, $factory, $this->container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function initRoutes(): void
    {
        $factory = $this->container->get(MiddlewareFactory::class);
        (require realpath(__DIR__ . '/../../config/routes.php'))($this->app, $factory, $this->container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(EntityManagerInterface::class);
    }

    protected function getContainer(): ContainerInterface|ServiceManager
    {
        return $this->container;
    }

    protected function get(
        string $uri,
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        $request = $this->createRequest(
            $uri,
            RequestMethodInterface::METHOD_GET,
            [],
            $queryParams,
            $uploadedFiles,
            $headers,
            $cookies,
        );

        return $this->getResponse($request);
    }

    protected function post(
        string $uri,
        array $parsedBody = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        $request = $this->createRequest(
            $uri,
            RequestMethodInterface::METHOD_POST,
            $parsedBody,
            $queryParams,
            $uploadedFiles,
            $headers,
            $cookies
        );

        return $this->getResponse($request);
    }

    protected function patch(
        string $uri,
        array $parsedBody = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        $request = $this->createRequest(
            $uri,
            RequestMethodInterface::METHOD_PATCH,
            $parsedBody,
            $queryParams,
            $uploadedFiles,
            $headers,
            $cookies
        );

        return $this->getResponse($request);
    }

    protected function put(
        string $uri,
        array $parsedBody = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        $request = $this->createRequest(
            $uri,
            RequestMethodInterface::METHOD_PUT,
            $parsedBody,
            $queryParams,
            $uploadedFiles,
            $headers,
            $cookies
        );

        return $this->getResponse($request);
    }

    protected function delete(
        string $uri,
        array $queryParams = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        $request = $this->createRequest(
            $uri,
            RequestMethodInterface::METHOD_DELETE,
            [],
            $queryParams,
            [],
            $headers,
            $cookies
        );

        return $this->getResponse($request);
    }

    private function createRequest(
        string $uri,
        string $method,
        array $parsedBody = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = [],
        array $serverParams = [],
        string $body = 'php://input',
        string $protocol = '1.1'
    ): ServerRequestInterface {
        if (method_exists($this, 'isAuthenticated') && $this->isAuthenticated()) {
            $headers = array_merge($headers, $this->getAuthorizationHeader());
        }

        return new ServerRequest(
            $serverParams,
            $uploadedFiles,
            $uri,
            $method,
            $body,
            $headers,
            $cookies,
            $queryParams,
            $parsedBody,
            $protocol,
        );
    }

    private function getResponse(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->app->handle($request);
        $response->getBody()->rewind();

        return $response;
    }

    protected function assertResponseOk(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    protected function assertResponseSuccessful(ResponseInterface $response): void
    {
        $this->assertBetween(
            $response->getStatusCode(),
            StatusCodeInterface::STATUS_OK,
            StatusCodeInterface::STATUS_MULTIPLE_CHOICES
        );
    }

    protected function assertResponseUnauthorized(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
    }

    protected function assertResponseForbidden(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }

    protected function assertResponseBadRequest(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_BAD_REQUEST, $response->getStatusCode());
    }

    protected function assertResponseNotFound(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
    }

    protected function assertBetween(int $value, int $from, int $to): void
    {
        $this->assertThat(
            $value,
            $this->logicalAnd(
                $this->greaterThan($from),
                $this->lessThan($to)
            )
        );
    }

    protected function replaceService(string $service, object $mockInstance): void
    {
        $this->getContainer()->setAllowOverride(true);
        $this->getContainer()->setService($service, $mockInstance);
        $this->getContainer()->setAllowOverride(false);
    }

    protected function getValidUserData(array $data = []): array
    {
        return [
            'detail'          => [
                'firstName' => $data['detail']['firstName'] ?? 'First',
                'lastName'  => $data['detail']['lastName'] ?? 'Last',
                'email'     => $data['detail']['email'] ?? 'user@dotkernel.com',
            ],
            'identity'        => $data['identity'] ?? 'user@dotkernel.com',
            'password'        => $data['password'] ?? self::DEFAULT_PASSWORD,
            'passwordConfirm' => $data['password'] ?? self::DEFAULT_PASSWORD,
            'status'          => $data['status'] ?? User::STATUS_ACTIVE,
        ];
    }

    protected function getInvalidUserData(): array
    {
        return [
            'detail'   => [
                'firstName' => 'invalid',
                'lastName'  => 'invalid',
                'email'     => 'invalid@dotkernel.com',
            ],
            'identity' => 'invalid',
            'password' => 'invalid',
            'status'   => Admin::STATUS_INACTIVE,
        ];
    }

    protected function getValidAdminData(): array
    {
        return [
            'firstName' => 'First',
            'identity'  => 'admin@dotkernel.com',
            'lastName'  => 'Last',
            'password'  => self::DEFAULT_PASSWORD,
            'status'    => Admin::STATUS_ACTIVE,
        ];
    }

    protected function getInvalidAdminData(): array
    {
        return [
            'firstName' => 'invalid',
            'identity'  => 'invalid',
            'lastName'  => 'invalid',
            'password'  => 'invalid',
            'status'    => Admin::STATUS_INACTIVE,
        ];
    }

    protected function getValidFrontendAccessTokenCredentials(array $data = []): array
    {
        $userData = $this->getValidUserData();
        return [
            'client_id'     => 'frontend',
            'client_secret' => 'frontend',
            'grant_type'    => 'password',
            'password'      => $data['password'] ?? $userData['password'],
            'scope'         => 'api',
            'username'      => $data['username'] ?? $userData['identity'],
        ];
    }

    protected function getInvalidFrontendAccessTokenCredentials(): array
    {
        return [
            'client_id'     => 'frontend',
            'client_secret' => 'frontend',
            'grant_type'    => 'password',
            'password'      => 'invalid',
            'scope'         => 'api',
            'username'      => 'invalid@dotkernel.com',
        ];
    }

    protected function getValidFrontendRefreshTokenCredentials(): array
    {
        return [
            'grant_type'    => 'refresh_token',
            'client_id'     => 'frontend',
            'client_secret' => 'frontend',
            'scope'         => 'api',
            'refresh_token' => $this->getRefreshToken(),
        ];
    }

    protected function getInvalidFrontendRefreshTokenCredentials(): array
    {
        return [
            'grant_type'    => 'refresh_token',
            'client_id'     => 'frontend',
            'client_secret' => 'frontend',
            'scope'         => 'api',
            'refresh_token' => 'invalid',
        ];
    }

    protected function getValidAdminAccessTokenCredentials(array $data = []): array
    {
        $adminData = $this->getValidAdminData();
        return [
            'client_id'     => 'admin',
            'client_secret' => 'admin',
            'grant_type'    => 'password',
            'password'      => $data['password'] ?? $adminData['password'],
            'scope'         => 'api',
            'username'      => $data['username'] ?? $adminData['identity'],
        ];
    }

    protected function getInvalidAdminAccessTokenCredentials(): array
    {
        return [
            'client_id'     => 'admin',
            'client_secret' => 'admin',
            'grant_type'    => 'password',
            'password'      => 'invalid',
            'scope'         => 'api',
            'username'      => 'invalid@dotkernel.com',
        ];
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function createAdmin(): Admin
    {
        $adminRoleRepository = $this->getEntityManager()->getRepository(AdminRole::class);

        /** @var RoleInterface $adminRole */
        $adminRole = $adminRoleRepository->findOneBy(['name' => AdminRole::ROLE_ADMIN]);

        $data = $this->getValidAdminData();

        $admin = (new Admin())
            ->setIdentity($data['identity'])
            ->usePassword($data['password'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setStatus($data['status'])
            ->addRole($adminRole);

        $this->getEntityManager()->persist($admin);
        $this->getEntityManager()->flush();

        return $admin;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function createUser(array $data = []): User
    {
        $userRoleRepository = $this->getEntityManager()->getRepository(UserRole::class);

        /** @var RoleInterface $userRole */
        $userRole = $userRoleRepository->findOneBy(['name' => UserRole::ROLE_USER]);

        $userData = $this->getValidUserData();

        $user       = new User();
        $userDetail = (new UserDetail())
            ->setUser($user)
            ->setFirstName($data['detail']['firstName'] ?? $userData['detail']['firstName'])
            ->setLastName($data['detail']['lastName'] ?? $userData['detail']['lastName'])
            ->setEmail($data['detail']['email'] ?? $userData['detail']['email']);

        $user
            ->setDetail($userDetail)
            ->addRole($userRole)
            ->setIdentity($data['identity'] ?? $userData['identity'])
            ->usePassword($data['password'] ?? $userData['password'])
            ->setStatus($data['status'] ?? $userData['status']);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }
}
