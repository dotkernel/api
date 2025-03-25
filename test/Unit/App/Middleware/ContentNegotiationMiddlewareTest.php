<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Middleware;

use Api\App\Middleware\ContentNegotiationMiddleware;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ContentNegotiationMiddlewareTest extends TestCase
{
    private ContentNegotiationMiddleware $contentNegotiationMiddleware;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private RouteResult $routeResult;

    private const ROUTE_NAME = 'test.route';

    private const CONFIG
        = [
            'test.route' => [
                'Accept'       => [
                    'application/json',
                    'application/hal+json',
                ],
                'Content-Type' => [
                    'application/json',
                    'application/hal+json',
                ],
            ],
        ];

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->handler = $this->createMock(RequestHandlerInterface::class);

        $this->routeResult = RouteResult::fromRoute(
            new Route(
                '/test-route',
                $this->createMock(MiddlewareInterface::class),
                name: 'test.route'
            )
        );

        $this->request = new ServerRequest();

        $this->contentNegotiationMiddleware = new ContentNegotiationMiddleware(self::CONFIG);
    }

    public function testWrongAccept(): void
    {
        $request = $this->request->withAttribute(
            RouteResult::class,
            $this->routeResult
        );
        $request = $request->withHeader('Accept', 'text/html');
        $this->assertSame(
            StatusCodeInterface::STATUS_NOT_ACCEPTABLE,
            $this->contentNegotiationMiddleware->process($request, $this->handler)->getStatusCode()
        );
    }

    public function testWrongContentType(): void
    {
        $request = $this->request->withAttribute(
            RouteResult::class,
            $this->routeResult
        );
        $request = $request->withHeader('Accept', 'application/hal+json');
        $request = $request->withHeader('Content-Type', 'text/html');
        $this->assertSame(
            StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE,
            $this->contentNegotiationMiddleware->process($request, $this->handler)->getStatusCode()
        );
    }

    public function testCannotResolveRepresentation(): void
    {
        $request = $this->request->withAttribute(
            RouteResult::class,
            $this->routeResult
        );
        $request = $request->withHeader('Accept', 'application/json');
        $request = $request->withHeader('Content-Type', 'application/json');
        $this->assertSame(
            StatusCodeInterface::STATUS_NOT_ACCEPTABLE,
            $this->contentNegotiationMiddleware->process($request, $this->handler)->getStatusCode()
        );
    }

    public function testFormatAcceptRequest(): void
    {
        $accept = $this->contentNegotiationMiddleware->formatAcceptRequest('application/json');

        $this->assertNotEmpty($accept);
        $this->assertSame(['application/json'], $accept);
    }

    public function testCheckAccept(): void
    {
        $this->assertTrue(
            $this->contentNegotiationMiddleware->checkAccept(
                self::ROUTE_NAME,
                ['*/*']
            )
        );
        $this->assertTrue(
            $this->contentNegotiationMiddleware->checkAccept(
                self::ROUTE_NAME,
                ['application/json']
            )
        );
        $this->assertFalse(
            $this->contentNegotiationMiddleware->checkAccept(self::ROUTE_NAME, ['text/html'])
        );
    }

    public function testCheckContentType(): void
    {
        $this->assertTrue(
            $this->contentNegotiationMiddleware->checkContentType(self::ROUTE_NAME, '')
        );

        $this->assertTrue(
            $this->contentNegotiationMiddleware->checkContentType(
                self::ROUTE_NAME,
                'application/json'
            )
        );
        $this->assertFalse(
            $this->contentNegotiationMiddleware->checkContentType(self::ROUTE_NAME, 'text/html')
        );
    }
}
