<?php

namespace AppTest\Functional;

use AppTest\Functional\Helper\AuthenticationTokenHelper;
use Doctrine\ORM\EntityManagerInterface;
use Fig\Http\Message\RequestMethodInterface;
use Laminas\Diactoros\ServerRequest;
use Fig\Http\Message\StatusCodeInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
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

    private AuthenticationTokenHelper $authenticationTokens;

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

        $response->getBody()->rewind();
        if (StatusCodeInterface::STATUS_OK !== $response->getStatusCode()) {
            $this->fail($response->getBody()->getContents());
        }

        $body = json_decode($response->getBody()->getContents(),true);
        $this->authenticationTokens = new AuthenticationTokenHelper($body['access_token'], $body['refresh_token']);

        return $this;
    }

    protected function getAuthenticationTokens(): AuthenticationTokenHelper
    {
        return $this->authenticationTokens;
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
        if (! empty($this->getAuthenticationTokens()->getAccessToken())) {
            $headers = array_merge($headers, $this->getAuthenticationTokens()->getAuthorizationHeader());
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
