<?php

namespace AppTest\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Reports\Status;

/**
 * Class AbstractFunctionalTest
 * @package Unit
 */
abstract class AbstractFunctionalTest extends TestCase
{
    protected ContainerInterface $container;

    protected Application $app;

    public function setUp(): void
    {
        parent::setUp();

        $this->enableTestMode();

        $this->initContainer();
        $this->initApp();
        $this->initPipeline();
        $this->initRoutes();

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
        $this->container = require realpath(__DIR__ . '/../../../../config/container.php');
    }

    private function initApp(): void
    {
        $this->app = $this->container->get(Application::class);
    }

    private function initPipeline(): void
    {
        $factory = $this->container->get(MiddlewareFactory::class);
        (require realpath(__DIR__ . '/../../../../config/pipeline.php'))($this->app, $factory, $this->container);
    }

    private function initRoutes(): void
    {
        $factory = $this->container->get(MiddlewareFactory::class);
        (require realpath(__DIR__ . '/../../../../config/routes.php'))($this->app, $factory, $this->container);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(EntityManagerInterface::class);
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function get
    (
        string $uri,
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface
    {
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

    protected function post
    (
        string $uri,
        array $parsedBody = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface
    {
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

    protected function patch
    (
        string $uri,
        array $parsedBody = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface
    {
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

    protected function put
    (
        string $uri,
        array $parsedBody = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface
    {
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

    protected function delete
    (
        string $uri,
        array $queryParams = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface
    {
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
     * @param string $uri
     * @param string $method
     * @param array $parsedBody
     * @param array $queryParams
     * @param array $uploadedFiles
     * @param array $headers
     * @param array $cookies
     * @param array $serverParams
     * @param string $body
     * @param string $protocol
     * @return ServerRequestInterface
     */
    private function createRequest
    (
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
    ): ServerRequestInterface
    {
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

    /**
     *
     * Process response and set cursor at position(0)
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
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

    protected function assertResponseSuccessful(ResponseInterface $response)
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

    protected function assertResponseNotFound(ResponseInterface $response)
    {
        $this->assertSame(StatusCodeInterface::STATUS_NOT_FOUND, $response->getStatusCode());
    }

    protected function assertBetween($value, $from, $to)
    {
        $this->assertThat(
            $value,
            $this->logicalAnd(
                $this->greaterThan($from),
                $this->lessThan($to)
            )
        );
    }

    /**
     * Replaces an actual service with a mock instance
     *
     * @param $service
     * @param $mockInstance
     */
    protected function replaceService($service, $mockInstance)
    {
        $this->getContainer()->setAllowOverride(true);
        $this->getContainer()->setService($service, $mockInstance);
        $this->getContainer()->setAllowOverride(false);
    }
}
