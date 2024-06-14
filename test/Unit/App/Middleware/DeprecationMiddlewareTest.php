<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Middleware;

use Api\App\Attribute\MethodDeprecation;
use Api\App\Attribute\ResourceDeprecation;
use Api\App\Exception\DeprecationConflictException;
use Api\App\Message;
use Api\App\Middleware\DeprecationMiddleware;
use Api\App\Middleware\DeprecationMiddleware as Subject;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Stratigility\MiddlewarePipe;
use Mezzio\Middleware\LazyLoadingMiddleware;
use Mezzio\MiddlewareContainer;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;

use function sprintf;

class DeprecationMiddlewareTest extends TestCase
{
    private Subject $subject;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private ResponseInterface $response;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->handler  = $this->createMock(RequestHandlerInterface::class);
        $this->request  = $this->createMock(ServerRequestInterface::class);
        $this->response = new EmptyResponse();
        $this->subject  = new Subject();
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function testThrowsDeprecationConflictException()
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
            DeprecationMiddleware::RESOURCE_DEPRECATION_ATTRIBUTE,
            DeprecationMiddleware::METHOD_DEPRECATION_ATTRIBUTE
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
        $this->handler->method('handle')->with($this->request)->willReturn($this->response);

        $response = $this->subject->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertIsArray($response->getHeaders());
        $this->assertTrue($response->hasHeader('sunset'));
        $this->assertTrue($response->hasHeader('link'));
        $this->assertSame('2038-01-01', $response->getHeader('sunset')[0]);
        $this->assertSame('test-link', $response->getHeader('link')[0]);
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
        $this->handler->method('handle')->with($this->request)->willReturn($this->response);

        $response = $this->subject->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertIsArray($response->getHeaders());
        $this->assertTrue($response->hasHeader('sunset'));
        $this->assertTrue($response->hasHeader('link'));
        $this->assertSame('2038-01-01', $response->getHeader('sunset')[0]);
        $this->assertSame('test-link', $response->getHeader('link')[0]);
    }
}
