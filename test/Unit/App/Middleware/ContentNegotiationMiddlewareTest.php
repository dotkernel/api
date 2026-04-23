<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Middleware;

use Api\App\Exception\NotAcceptableException;
use Api\App\Exception\UnsupportedMediaTypeException;
use Api\App\Middleware\ContentNegotiationMiddleware;
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

    private const array CONFIG
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
        $this->handler = $this->createStub(RequestHandlerInterface::class);

        $this->routeResult = RouteResult::fromRoute(
            new Route(
                '/test-route',
                $this->createStub(MiddlewareInterface::class),
                name: 'test.route'
            )
        );

        $this->request = new ServerRequest();

        $this->contentNegotiationMiddleware = new ContentNegotiationMiddleware(self::CONFIG);
    }

    /**
     * @throws UnsupportedMediaTypeException
     * @throws NotAcceptableException
     */
    public function testWrongAccept(): void
    {
        $request = $this->request
            ->withAttribute(RouteResult::class, $this->routeResult)
            ->withHeader('Accept', 'text/html');

        $this->expectException(NotAcceptableException::class);

        $this->contentNegotiationMiddleware->process($request, $this->handler);
    }

    /**
     * @throws NotAcceptableException
     * @throws UnsupportedMediaTypeException
     */
    public function testWrongContentType(): void
    {
        $request = $this->request
            ->withAttribute(RouteResult::class, $this->routeResult)
            ->withHeader('Accept', 'application/hal+json')
            ->withHeader('Content-Type', 'text/html');

        $this->expectException(UnsupportedMediaTypeException::class);

        $this->contentNegotiationMiddleware->process($request, $this->handler);
    }

    /**
     * @throws NotAcceptableException
     * @throws UnsupportedMediaTypeException
     */
    public function testCannotResolveRepresentation(): void
    {
        $request = $this->request
            ->withAttribute(RouteResult::class, $this->routeResult)
            ->withHeader('Accept', 'text/html')
            ->withHeader('Content-Type', 'application/json');

        $this->expectException(NotAcceptableException::class);

        $this->contentNegotiationMiddleware->process($request, $this->handler);
    }
}
