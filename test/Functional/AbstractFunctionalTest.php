<?php

declare(strict_types=1);

namespace ApiTest\Functional;

use ApiTest\Functional\Traits\AuthenticationTrait;
use ApiTest\Functional\Traits\DatabaseTrait;
use Core\Admin\Entity\Admin;
use Core\Admin\Entity\AdminRole;
use Core\Admin\Enum\AdminRoleEnum;
use Core\Admin\Enum\AdminStatusEnum;
use Core\App\Entity\RoleInterface;
use Core\User\Entity\User;
use Core\User\Entity\UserDetail;
use Core\User\Entity\UserRole;
use Core\User\Enum\UserRoleEnum;
use Core\User\Enum\UserStatusEnum;
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
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

use function array_key_exists;
use function array_merge;
use function getenv;
use function putenv;
use function realpath;

class AbstractFunctionalTest extends TestCase
{
    use AuthenticationTrait;
    use DatabaseTrait;

    protected Application $app;
    protected ContainerInterface $container;
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

        $this->ensureTestMode();

        $this->runMigrations();
        $this->runSeeders();
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

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RuntimeException
     */
    private function ensureTestMode(): void
    {
        if (! $this->isTestMode()) {
            throw new RuntimeException(
                'You are running tests, but test mode is NOT enabled. Did you forget to create local.test.php?'
            );
        }

        if (! ($this->getEntityManager()->getConnection()->getParams()['memory'] ?? false)) {
            throw new RuntimeException(
                'You are running tests in a non in-memory database. Did you forget to create local.test.php?'
            );
        }
    }

    /**
     * @param non-empty-string $uri
     * @param array<non-empty-string, mixed> $queryParams
     * @param array<non-empty-string, UploadedFileInterface> $uploadedFiles
     * @param array<non-empty-string, mixed> $headers
     * @param array<non-empty-string, mixed> $cookies
     */
    protected function get(
        string $uri,
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = ['Accept' => ['application/json, application/hal+json']],
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

    /**
     * @param non-empty-string $uri
     * @param array<non-empty-string, mixed> $parsedBody
     * @param array<non-empty-string, mixed> $queryParams
     * @param array<non-empty-string, UploadedFileInterface> $uploadedFiles
     * @param array<non-empty-string, mixed> $headers
     * @param array<non-empty-string, mixed> $cookies
     */
    protected function post(
        string $uri,
        array $parsedBody = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = ['Accept' => ['application/json, application/hal+json']],
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

    /**
     * @param non-empty-string $uri
     * @param array<non-empty-string, mixed> $parsedBody
     * @param array<non-empty-string, mixed> $queryParams
     * @param array<non-empty-string, UploadedFileInterface> $uploadedFiles
     * @param array<non-empty-string, mixed> $headers
     * @param array<non-empty-string, mixed> $cookies
     */
    protected function patch(
        string $uri,
        array $parsedBody = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = ['Accept' => ['application/json, application/hal+json']],
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

    /**
     * @param non-empty-string $uri
     * @param array<non-empty-string, mixed> $parsedBody
     * @param array<non-empty-string, mixed> $queryParams
     * @param array<non-empty-string, UploadedFileInterface> $uploadedFiles
     * @param array<non-empty-string, mixed> $headers
     * @param array<non-empty-string, mixed> $cookies
     */
    protected function put(
        string $uri,
        array $parsedBody = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = ['Accept' => ['application/json, application/hal+json']],
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

    /**
     * @param non-empty-string $uri
     * @param array<non-empty-string, mixed> $queryParams
     * @param array<non-empty-string, mixed> $headers
     * @param array<non-empty-string, mixed> $cookies
     */
    protected function delete(
        string $uri,
        array $queryParams = [],
        array $headers = ['Accept' => 'application/json, application/hal+json, text/plain'],
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

    /**
     * @param non-empty-string $uri
     * @param non-empty-string $method
     * @param array<non-empty-string, mixed> $parsedBody
     * @param array<non-empty-string, mixed> $queryParams
     * @param array<non-empty-string, UploadedFileInterface> $uploadedFiles
     * @param array<non-empty-string, mixed> $headers
     * @param array<non-empty-string, mixed> $cookies
     * @param array<non-empty-string, mixed> $serverParams
     * @param non-empty-string $body
     * @param non-empty-string $protocol
     */
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
        if ($this->isAuthenticated()) {
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

    protected function assertResponseCreated(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());
    }

    protected function assertResponseSuccessful(ResponseInterface $response): void
    {
        $this->assertBetween(
            $response->getStatusCode(),
            StatusCodeInterface::STATUS_OK,
            StatusCodeInterface::STATUS_MULTIPLE_CHOICES
        );
    }

    protected function assertResponseBadRequest(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_BAD_REQUEST, $response->getStatusCode());
    }

    protected function assertResponseUnauthorized(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_UNAUTHORIZED, $response->getStatusCode());
    }

    protected function assertResponseForbidden(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_FORBIDDEN, $response->getStatusCode());
    }

    protected function assertResponseNotFound(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
    }

    protected function assertResponseConflict(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_CONFLICT, $response->getStatusCode());
    }

    protected function assertResponseGone(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_GONE, $response->getStatusCode());
    }

    protected function assertResponseNoContent(ResponseInterface $response): void
    {
        $this->assertSame(StatusCodeInterface::STATUS_NO_CONTENT, $response->getStatusCode());
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
        /** @var ServiceManager $container */
        $container = $this->getContainer();
        $container->setAllowOverride(true);
        $container->setService($service, $mockInstance);
        $container->setAllowOverride(false);
    }

    /**
     * @phpstan-param array{
     *     detail?: array{
     *          firstName: non-empty-string,
     *          lastName: non-empty-string,
     *          email: non-empty-string,
     *     },
     *     identity?: non-empty-string,
     *     password?: non-empty-string,
     *     passwordConfirm?: non-empty-string,
     *     status?: non-empty-string,
     * } $data
     * @return array{
     *     detail: array{
     *          firstName: non-empty-string,
     *          lastName: non-empty-string,
     *          email: non-empty-string,
     *     },
     *     identity: non-empty-string,
     *     password: non-empty-string,
     *     passwordConfirm: non-empty-string,
     *     status: \Core\User\Enum\UserStatusEnum,
     *     roles: array{id: non-empty-string}[]
     * }
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getValidUserData(array $data): array
    {
        $userRole = $this->findUserRole(UserRoleEnum::User);
        $this->assertInstanceOf(UserRole::class, $userRole);

        $status = null;
        if (array_key_exists('status', $data)) {
            $status = UserStatusEnum::tryFrom($data['status']);
        }
        if ($status === null) {
            $status = UserStatusEnum::Active;
        }

        return [
            'detail'          => [
                'firstName' => $data['detail']['firstName'] ?? 'First',
                'lastName'  => $data['detail']['lastName'] ?? 'Last',
                'email'     => $data['detail']['email'] ?? 'user@dotkernel.com',
            ],
            'identity'        => $data['identity'] ?? 'user@dotkernel.com',
            'password'        => $data['password'] ?? self::DEFAULT_PASSWORD,
            'passwordConfirm' => $data['password'] ?? self::DEFAULT_PASSWORD,
            'status'          => $status,
            'roles'           => [
                ['id' => $userRole->getId()->toString()],
            ],
        ];
    }

    /**
     * @return array{
     *     detail: array{
     *          firstName: non-empty-string,
     *          lastName: non-empty-string,
     *          email: non-empty-string,
     *     },
     *     identity: non-empty-string,
     *     password: non-empty-string,
     *     status: UserStatusEnum::Pending,
     * }
     */
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
            'status'   => UserStatusEnum::Pending,
        ];
    }

    /**
     * @return array{
     *     firstName: non-empty-string,
     *     identity: non-empty-string,
     *     lastName: non-empty-string,
     *     password: non-empty-string,
     *     status: AdminStatusEnum::Active,
     * }
     */
    protected function getValidAdminData(): array
    {
        return [
            'firstName' => 'First',
            'identity'  => 'admin@dotkernel.com',
            'lastName'  => 'Last',
            'password'  => self::DEFAULT_PASSWORD,
            'status'    => AdminStatusEnum::Active,
        ];
    }

    /**
     * @return array{
     *     firstName: non-empty-string,
     *     identity: non-empty-string,
     *     lastName: non-empty-string,
     *     password: non-empty-string,
     *     status: AdminStatusEnum::Inactive,
     * }
     */
    protected function getInvalidAdminData(): array
    {
        return [
            'firstName' => 'invalid',
            'identity'  => 'invalid',
            'lastName'  => 'invalid',
            'password'  => 'invalid',
            'status'    => AdminStatusEnum::Inactive,
        ];
    }

    /**
     * @param array<non-empty-string, mixed> $data
     * @return array{
     *     client_id: non-empty-string,
     *     client_secret: non-empty-string,
     *     grant_type: non-empty-string,
     *     password: non-empty-string,
     *     scope: non-empty-string,
     *     username: non-empty-string,
     * }
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getValidFrontendAccessTokenCredentials(array $data = []): array
    {
        $userData = $this->getValidUserData([]);
        return [
            'client_id'     => 'frontend',
            'client_secret' => 'frontend',
            'grant_type'    => 'password',
            'password'      => $data['password'] ?? $userData['password'],
            'scope'         => 'api',
            'username'      => $data['username'] ?? $userData['identity'],
        ];
    }

    /**
     * @return array{
     *     client_id: non-empty-string,
     *     client_secret: non-empty-string,
     *     grant_type: non-empty-string,
     *     password: non-empty-string,
     *     scope: non-empty-string,
     *     username: non-empty-string,
     * }
     */
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

    /**
     * @return array{
     *     grant_type: non-empty-string,
     *     client_id: non-empty-string,
     *     client_secret: non-empty-string,
     *     scope: non-empty-string,
     *     refresh_token: string|null,
     * }
     */
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

    /**
     * @return array{
     *     grant_type: non-empty-string,
     *     client_id: non-empty-string,
     *     client_secret: non-empty-string,
     *     scope: non-empty-string,
     *     refresh_token: non-empty-string,
     * }
     */
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

    /**
     * @param array<non-empty-string, mixed> $data
     * @return array{
     *     client_id: non-empty-string,
     *     client_secret: non-empty-string,
     *     grant_type: non-empty-string,
     *     password: non-empty-string,
     *     scope: non-empty-string,
     *     username: non-empty-string,
     * }
     */
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

    /**
     * @return array{
     *     client_id: non-empty-string,
     *     client_secret: non-empty-string,
     *     grant_type: non-empty-string,
     *     password: non-empty-string,
     *     scope: non-empty-string,
     *     username: non-empty-string,
     * }
     */
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
        $adminRole = $adminRoleRepository->findOneBy(['name' => AdminRoleEnum::Admin]);

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
     * @param array<non-empty-string, mixed> $data
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function createUser(array $data = []): User
    {
        $userRole = $this->findUserRole(UserRoleEnum::User);
        $this->assertInstanceOf(UserRole::class, $userRole);

        $userData = $this->getValidUserData([]);

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

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function findUserRole(UserRoleEnum $role): ?UserRole
    {
        $userRole = $this->getEntityManager()->getRepository(UserRole::class)->findOneBy(['name' => $role]);
        if ($userRole instanceof UserRole) {
            return $userRole;
        }

        return null;
    }
}
