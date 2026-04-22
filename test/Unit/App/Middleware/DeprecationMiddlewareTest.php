<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Middleware;

use Api\App\Attribute\ResourceDeprecation;
use Api\App\Exception\ConflictException;
use Api\App\Middleware\DeprecationMiddleware;
use Fig\Http\Message\RequestMethodInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Stratigility\MiddlewarePipe;
use Mezzio\Middleware\LazyLoadingMiddleware;
use Mezzio\MiddlewareContainer;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

use function implode;
use function sprintf;

class DeprecationMiddlewareTest extends TestCase
{
    private DeprecationMiddleware $deprecationMiddleware;
    private ServerRequestInterface&Stub $request;
    private RequestHandlerInterface&Stub $handler;
    private ResponseInterface $response;

    private const VERSIONING_CONFIG = [
        'documentation_url' => 'www.example.com',
    ];

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->handler  = $this->createStub(RequestHandlerInterface::class);
        $this->request  = $this->createStub(ServerRequestInterface::class);
        $this->response = new EmptyResponse();

        $this->deprecationMiddleware = new DeprecationMiddleware(self::VERSIONING_CONFIG);
    }

    /**
     * @throws Exception
     * @throws ConflictException
     * @throws ReflectionException
     */
    public function testLazyLoadingMiddleware(): void
    {
        $handler = new #[ResourceDeprecation(
            sunset: '2038-01-01',
            link: 'test-link',
            deprecationReason: 'test-deprecation-reason',
            rel: 'test-rel',
            type: 'test-type',
        )] class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $routeResult           = $this->createStub(RouteResult::class);
        $route                 = $this->createStub(Route::class);
        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createStub(MiddlewareContainer::class),
            $handler::class,
        );

        $route->method('getMiddleware')->willReturn($lazyLoadingMiddleware);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_GET);
        $this->handler->method('handle')->willReturn($this->response);

        $response = $this->deprecationMiddleware->process($this->request, $this->handler);

        $this->assertTrue($response->hasHeader('sunset'));
        $this->assertTrue($response->hasHeader('link'));
        $this->assertSame('2038-01-01', $response->getHeader('sunset')[0]);
        $this->assertSame($this->formatLink('test-link', [
            'rel'  => 'test-rel',
            'type' => 'test-type',
        ]), $response->getHeader('link')[0]);
    }

    /**
     * @throws ConflictException
     * @throws Exception
     * @throws ReflectionException
     */
    public function testMiddlewarePipeline(): void
    {
        $handler = new #[ResourceDeprecation(
            sunset: '2038-01-01',
            link: 'test-link',
            deprecationReason: 'test-deprecation-reason',
            rel: 'test-rel',
            type: 'test-type',
        )] class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $middleware = new class implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $handler->handle($request);
            }
        };

        $routeResult = $this->createStub(RouteResult::class);
        $route       = $this->createStub(Route::class);

        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createStub(MiddlewareContainer::class),
            $middleware::class
        );

        $lazyLoadingMiddlewareHandler = new LazyLoadingMiddleware(
            $this->createStub(MiddlewareContainer::class),
            $handler::class,
        );

        $middlewarePipeline = new MiddlewarePipe();
        $middlewarePipeline->pipe($lazyLoadingMiddleware);
        $middlewarePipeline->pipe($lazyLoadingMiddlewareHandler);

        $route->method('getMiddleware')->willReturn($middlewarePipeline);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_GET);
        $this->handler->method('handle')->willReturn($this->response);

        $response = $this->deprecationMiddleware->process($this->request, $this->handler);

        $this->assertTrue($response->hasHeader('sunset'));
        $this->assertTrue($response->hasHeader('link'));
        $this->assertSame('2038-01-01', $response->getHeader('sunset')[0]);
        $this->assertSame($this->formatLink('test-link', [
            'rel'  => 'test-rel',
            'type' => 'test-type',
        ]), $response->getHeader('link')[0]);
    }

    /**
     * @throws ConflictException
     * @throws Exception
     * @throws ReflectionException
     */
    public function testDefaultLink(): void
    {
        $handler = new #[ResourceDeprecation] class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $routeResult           = $this->createStub(RouteResult::class);
        $route                 = $this->createStub(Route::class);
        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createStub(MiddlewareContainer::class),
            $handler::class,
        );

        $route->method('getMiddleware')->willReturn($lazyLoadingMiddleware);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_GET);
        $this->handler->method('handle')->willReturn($this->response);

        $response = $this->deprecationMiddleware->process($this->request, $this->handler);

        $this->assertTrue($response->hasHeader('link'));
        $this->assertFalse($response->hasHeader('sunset'));

        $this->assertSame($this->formatLink(self::VERSIONING_CONFIG['documentation_url'], [
            'rel'  => 'sunset',
            'type' => 'text/html',
        ]), $response->getHeader('link')[0]);
    }

    /**
     * @throws ConflictException
     * @throws Exception
     * @throws ReflectionException
     */
    public function testDynamicLink(): void
    {
        $handler = new #[ResourceDeprecation(link : 'dynamic-link')] class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $routeResult           = $this->createStub(RouteResult::class);
        $route                 = $this->createStub(Route::class);
        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createStub(MiddlewareContainer::class),
            $handler::class,
        );

        $route->method('getMiddleware')->willReturn($lazyLoadingMiddleware);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_GET);
        $this->handler->method('handle')->willReturn($this->response);

        $response = $this->deprecationMiddleware->process($this->request, $this->handler);

        $this->assertTrue($response->hasHeader('link'));
        $this->assertFalse($response->hasHeader('sunset'));

        $this->assertSame($this->formatLink('dynamic-link', [
            'rel'  => 'sunset',
            'type' => 'text/html',
        ]), $response->getHeader('link')[0]);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     * @throws ConflictException
     */
    public function testSunset(): void
    {
        $handler = new #[ResourceDeprecation(sunset : '2038-01-01')] class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $routeResult           = $this->createStub(RouteResult::class);
        $route                 = $this->createStub(Route::class);
        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createStub(MiddlewareContainer::class),
            $handler::class,
        );

        $route->method('getMiddleware')->willReturn($lazyLoadingMiddleware);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_GET);
        $this->handler->method('handle')->willReturn($this->response);

        $response = (new DeprecationMiddleware([]))->process($this->request, $this->handler);

        $this->assertFalse($response->hasHeader('link'));
        $this->assertTrue($response->hasHeader('sunset'));

        $this->assertSame('2038-01-01', $response->getHeader('sunset')[0]);
    }

    /**
     * @param non-empty-string $baseLink
     * @param array<non-empty-string, mixed> $attribute
     * @return non-empty-string
     */
    private function formatLink(string $baseLink, array $attribute): string
    {
        $parts = [
            $baseLink,
        ];
        if (! empty($attribute['rel'])) {
            $parts[] = sprintf('rel="%s"', $attribute['rel']);
        }
        if (! empty($attribute['type'])) {
            $parts[] = sprintf('type="%s"', $attribute['type']);
        }

        return implode(';', $parts);
    }
}
