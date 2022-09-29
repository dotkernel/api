<?php

namespace AppTest\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Fig\Http\Message\RequestMethodInterface;
use Laminas\Diactoros\ServerRequest;
use Fig\Http\Message\StatusCodeInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
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

    protected array $authTokens = [];

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
        TestHelper::disableTestMode();

        parent::tearDown();
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

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(EntityManagerInterface::class);
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param string $uri
     * @param array $queryParams
     * @param array $uploadedFiles
     * @param array $headers
     * @param array $cookies
     * @return ResponseInterface
     */
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

    /**
     * @param string $uri
     * @param array $parsedBody
     * @param array $queryParams
     * @param array $uploadedFiles
     * @param array $headers
     * @param array $cookies
     * @return ResponseInterface
     */
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

    /**
     * @param string $identity
     * @param string $password
     * @param string $clientId
     * @param string $clientSecret
     * @param string $scope
     * @return $this
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function loginAs
    (
        string $identity,
        string $password,
        string $clientId = 'frontend',
        string $clientSecret = 'frontend',
        string $scope = 'api'
    ): self
    {
        $request = $this->createLoginRequest([
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
            'username' => $identity,
            'password' => $password,
        ]);

        $authorizationServer = $this->getContainer()->get(AuthorizationServer::class);
        $responseFactory = $this->getContainer()->get(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse();
        try {
            $response = $authorizationServer->respondToAccessTokenRequest($request, $responseFactory->createResponse());
        } catch (OAuthServerException $exception) {
            $response = $exception->generateHttpResponse($response);
        }

        $this->assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $response->getBody()->rewind();

        $body = json_decode($response->getBody()->getContents(),true);

        $this->assertArrayHasKey('access_token', $body);
        $this->assertArrayHasKey('refresh_token', $body);
        $this->assertNotEmpty($body['access_token']);
        $this->assertNotEmpty($body['refresh_token']);

        $this->authTokens = [
            'access_token' => 'Bearer ' . $body['access_token'],
            'refresh_token' => $body['refresh_token'],
        ];

        return $this;
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
        if (! empty($this->authTokens['access_token'])) {
            $headers = array_merge($headers, ['Authorization' => $this->authTokens['access_token']]);
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
     * @param array $bodyParams
     * @return ServerRequest
     */
    private function createLoginRequest(array $bodyParams): ServerRequest
    {
        return new ServerRequest(
            [],
            [],
            '',
            RequestMethodInterface::METHOD_POST,
            'php://input',
            [],
            [],
            [],
            $bodyParams,
            '1.1',
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

}
