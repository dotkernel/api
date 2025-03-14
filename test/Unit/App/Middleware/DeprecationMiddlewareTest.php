<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Middleware;

use Api\App\Attribute\MethodDeprecation;
use Api\App\Attribute\ResourceDeprecation;
use Api\App\Exception\DeprecationConflictException;
use Api\App\Handler\AbstractHandler;
use Api\App\Message;
use Api\App\Middleware\DeprecationMiddleware as Subject;
use Fig\Http\Message\RequestMethodInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Stratigility\MiddlewarePipe;
use Mezzio\Middleware\LazyLoadingMiddleware;
use Mezzio\MiddlewareContainer;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

use function implode;
use function rtrim;
use function sprintf;

class DeprecationMiddlewareTest extends TestCase
{
    private Subject $subject;
    private ServerRequestInterface|MockObject $request;
    private RequestHandlerInterface|MockObject $handler;
    private ResponseInterface $response;

    private const VERSIONING_CONFIG = [
        'documentation_url' => 'www.example.com',
    ];

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->handler  = $this->createMock(RequestHandlerInterface::class);
        $this->request  = $this->createMock(ServerRequestInterface::class);
        $this->response = new EmptyResponse();
        $this->subject  = new Subject(self::VERSIONING_CONFIG);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testThrowsDeprecationConflictException(): void
    {
        $handler = new #[ResourceDeprecation(
            sunset: '2038-01-01',
            link: 'test-link',
            deprecationReason: 'test-deprecation-reason',
        )] class implements RequestHandlerInterface {
            #[MethodDeprecation(
                sunset: '2038-01-01',
                link: 'test-link',
                deprecationReason: 'test-deprecation-reason',
            )]
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $routeResult           = $this->createMock(RouteResult::class);
        $route                 = $this->createMock(Route::class);
        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createMock(MiddlewareContainer::class),
            $handler::class,
        );

        $route->method('getMiddleware')->willReturn($lazyLoadingMiddleware);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->with(RouteResult::class)->willReturn($routeResult);
        $this->handler->method('handle')->with($this->request)->willReturn($this->response);

        $this->expectException(DeprecationConflictException::class);
        $this->expectExceptionMessage(sprintf(
            Message::RESTRICTION_DEPRECATION,
            Subject::RESOURCE_DEPRECATION_ATTRIBUTE,
            Subject::METHOD_DEPRECATION_ATTRIBUTE
        ));

        $this->subject->process($this->request, $this->handler);
    }

    /**
     * @throws Exception
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

        $routeResult           = $this->createMock(RouteResult::class);
        $route                 = $this->createMock(Route::class);
        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createMock(MiddlewareContainer::class),
            $handler::class,
        );

        $route->method('getMiddleware')->willReturn($lazyLoadingMiddleware);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->with(RouteResult::class)->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_GET);
        $this->handler->method('handle')->with($this->request)->willReturn($this->response);

        $response = $this->subject->process($this->request, $this->handler);

        $this->assertTrue($response->hasHeader('sunset'));
        $this->assertTrue($response->hasHeader('link'));
        $this->assertSame('2038-01-01', $response->getHeader('sunset')[0]);
        $this->assertSame($this->formatLink('test-link', [
            'rel'  => 'test-rel',
            'type' => 'test-type',
        ]), $response->getHeader('link')[0]);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
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

        $routeResult = $this->createMock(RouteResult::class);
        $route       = $this->createMock(Route::class);

        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createMock(MiddlewareContainer::class),
            $middleware::class
        );

        $lazyLoadingMiddlewareHandler = new LazyLoadingMiddleware(
            $this->createMock(MiddlewareContainer::class),
            $handler::class,
        );

        $middlewarePipeline = new MiddlewarePipe();
        $middlewarePipeline->pipe($lazyLoadingMiddleware);
        $middlewarePipeline->pipe($lazyLoadingMiddlewareHandler);

        $route->method('getMiddleware')->willReturn($middlewarePipeline);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->with(RouteResult::class)->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_GET);
        $this->handler->method('handle')->with($this->request)->willReturn($this->response);

        $response = $this->subject->process($this->request, $this->handler);

        $this->assertTrue($response->hasHeader('sunset'));
        $this->assertTrue($response->hasHeader('link'));
        $this->assertSame('2038-01-01', $response->getHeader('sunset')[0]);
        $this->assertSame($this->formatLink('test-link', [
            'rel'  => 'test-rel',
            'type' => 'test-type',
        ]), $response->getHeader('link')[0]);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testDeprecationMethodUsesRequestMethod(): void
    {
        $handler = new class extends AbstractHandler {
            #[MethodDeprecation(
                sunset: '2038-01-01',
                link: 'get-test-link',
                deprecationReason: 'get-test-deprecation-reason',
                rel: 'get-rel',
                type: 'get-type',
            )]
            public function get(): ResponseInterface
            {
                return new EmptyResponse();
            }

            #[MethodDeprecation(
                sunset: '2038-01-01',
                link: 'post-test-link',
                deprecationReason: 'post-test-deprecation-reason',
                rel: 'post-rel',
                type: 'post-type',
            )]
            public function post(): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $routeResult           = $this->createMock(RouteResult::class);
        $route                 = $this->createMock(Route::class);
        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createMock(MiddlewareContainer::class),
            $handler::class,
        );

        $route->method('getMiddleware')->willReturn($lazyLoadingMiddleware);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->with(RouteResult::class)->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_POST);
        $this->handler->method('handle')->with($this->request)->willReturn($this->response);

        $response = $this->subject->process($this->request, $this->handler);

        $this->assertTrue($response->hasHeader('sunset'));
        $this->assertTrue($response->hasHeader('link'));
        $this->assertSame('2038-01-01', $response->getHeader('sunset')[0]);
        $this->assertSame($this->formatLink('post-test-link', [
            'rel'  => 'post-rel',
            'type' => 'post-type',
        ]), $response->getHeader('link')[0]);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testDefaultLink(): void
    {
        $handler = new #[ResourceDeprecation] class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $routeResult           = $this->createMock(RouteResult::class);
        $route                 = $this->createMock(Route::class);
        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createMock(MiddlewareContainer::class),
            $handler::class,
        );

        $route->method('getMiddleware')->willReturn($lazyLoadingMiddleware);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->with(RouteResult::class)->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_GET);
        $this->handler->method('handle')->with($this->request)->willReturn($this->response);

        $response = $this->subject->process($this->request, $this->handler);

        $this->assertTrue($response->hasHeader('link'));
        $this->assertFalse($response->hasHeader('sunset'));

        $this->assertSame($this->formatLink(self::VERSIONING_CONFIG['documentation_url'], [
            'rel'  => 'sunset',
            'type' => 'text/html',
        ]), $response->getHeader('link')[0]);
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testDynamicLink(): void
    {
        $handler = new #[ResourceDeprecation(link : 'dynamic-link')] class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $routeResult           = $this->createMock(RouteResult::class);
        $route                 = $this->createMock(Route::class);
        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createMock(MiddlewareContainer::class),
            $handler::class,
        );

        $route->method('getMiddleware')->willReturn($lazyLoadingMiddleware);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->with(RouteResult::class)->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_GET);
        $this->handler->method('handle')->with($this->request)->willReturn($this->response);

        $response = $this->subject->process($this->request, $this->handler);

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
     */
    public function testSunset(): void
    {
        $handler = new #[ResourceDeprecation(sunset : '2038-01-01')] class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new EmptyResponse();
            }
        };

        $routeResult           = $this->createMock(RouteResult::class);
        $route                 = $this->createMock(Route::class);
        $lazyLoadingMiddleware = new LazyLoadingMiddleware(
            $this->createMock(MiddlewareContainer::class),
            $handler::class,
        );

        $route->method('getMiddleware')->willReturn($lazyLoadingMiddleware);
        $routeResult->method('isFailure')->willReturn(false);
        $routeResult->method('getMatchedRoute')->willReturn($route);
        $this->request->method('getAttribute')->with(RouteResult::class)->willReturn($routeResult);
        $this->request->method('getMethod')->willReturn(RequestMethodInterface::METHOD_GET);
        $this->handler->method('handle')->with($this->request)->willReturn($this->response);

        $response = (new Subject([]))->process($this->request, $this->handler);

        $this->assertFalse($response->hasHeader('link'));
        $this->assertTrue($response->hasHeader('sunset'));

        $this->assertSame('2038-01-01', $response->getHeader('sunset')[0]);
    }

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

        return rtrim(implode(';', $parts), ';');
    }
}
