<?php

namespace AppTest\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Fig\Http\Message\RequestMethodInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Http\Response;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractFunctionalTest
 * @package Unit
 */
abstract class AbstractFunctionalTest extends TestCase
{
    protected ContainerInterface $container;

    protected Application $app;

    private string $accessToken = '';

    public function setUp(): void
    {
        parent::setUp();

        TestHelper::enableTestMode();

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
        parent::tearDown();

        TestHelper::disableTestMode();
    }

    private function initContainer(): void
    {
        $this->container = require realpath(__DIR__ . '/../../../config/container.php');
    }

    private function initApp(): void
    {
        $this->app = $this->container->get(Application::class);
    }

    private function initPipeline(): void
    {
        $factory = $this->container->get(MiddlewareFactory::class);
        (require realpath(__DIR__ . '/../../../config/pipeline.php'))($this->app, $factory, $this->container);
    }

    private function initRoutes(): void
    {
        $factory = $this->container->get(MiddlewareFactory::class);
        (require realpath(__DIR__ . '/../../../config/routes.php'))($this->app, $factory, $this->container);
    }

    protected function app(): Application
    {
        return $this->app;
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
        array $params = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface
    {
        $queryParams = $params;
        $request = $this->createRequest(
            [],
            $uploadedFiles,
            $uri,
            RequestMethodInterface::METHOD_GET,
            'php://input',
            $headers,
            $cookies,
            $queryParams,
        );

        return $this->app->handle($request);
    }

    protected function post
    (
        string $uri,
        array $bodyParams = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface
    {
        $request = $this->createRequest(
            [],
            $uploadedFiles,
            $uri,
            RequestMethodInterface::METHOD_POST,
            'php://input',
            $headers,
            $cookies,
            $queryParams,
            $bodyParams,
        );

        return $this->app->handle($request);
    }

    protected function loginAs(string $identity, string $password): self
    {
        $response = $this->post('/security/generate-token', [
            'grant_type' => 'password',
            'client_id' => 'frontend',
            'client_secret' => 'frontend',
            'scope' => 'api',
            'username' => $identity,
            'password' => $password,
        ]);

        $this->assertSame(Response::STATUS_CODE_200, $response->getStatusCode());

        $body = json_decode((string)$response->getBody(),true);

        $this->assertArrayHasKey('access_token', $body);
        $this->assertNotEmpty($body['access_token']);

        $this->accessToken = 'Bearer ' . $body['access_token'];

        return $this;
    }

    private function createRequest
    (
        array $serverParams = [],
        array $uploadedFiles = [],
        string $uri = '',
        string $method = RequestMethodInterface::METHOD_GET,
        string $body = 'php://input',
        array $headers = [],
        array $cookies = [],
        array $queryParams = [],
        array $parsedBody = [],
        string $protocol = '1.1'
    ): ServerRequestInterface
    {
        if (! empty($this->accessToken)) {
            $headers = array_merge($headers, ['Authorization' => $this->accessToken]);
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
            $protocol
        );
    }
}
