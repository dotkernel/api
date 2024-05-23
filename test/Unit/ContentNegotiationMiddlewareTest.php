<?php

declare(strict_types=1);

namespace ApiTest\Unit;

use Api\App\Middleware\ContentNegotiationMiddleware as Subject;
use Laminas\Diactoros\ServerRequest;
use Laminas\Http\Response;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ContentNegotiationMiddlewareTest extends TestCase
{
    private Subject $subject;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private ResponseInterface $response;
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
        $this->handler  = $this->createMock(RequestHandlerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        $this->routeResult = RouteResult::fromRoute(
            new Route(
                '/test-route',
                $this->createMock(MiddlewareInterface::class),
                name: 'test.route'
            )
        );

        $this->request = new ServerRequest();

        $this->subject = new Subject(self::CONFIG);
    }

    public function testWrongAccept()
    {
        $request = $this->request->withAttribute(
            RouteResult::class,
            $this->routeResult
        );
        $request = $request->withHeader('Accept', 'text/html');
        $this->assertSame(
            Response::STATUS_CODE_406,
            $this->subject->process($request, $this->handler)->getStatusCode()
        );
    }

    public function testWrongContentType()
    {
        $request = $this->request->withAttribute(
            RouteResult::class,
            $this->routeResult
        );
        $request = $request->withHeader('Accept', 'application/hal+json');
        $request = $request->withHeader('Content-Type', 'text/html');
        $this->assertSame(
            Response::STATUS_CODE_415,
            $this->subject->process($request, $this->handler)->getStatusCode()
        );
    }

    public function testCannotResolveRepresentation()
    {
        $request = $this->request->withAttribute(
            RouteResult::class,
            $this->routeResult
        );
        $request = $request->withHeader('Accept', 'application/json');
        $request = $request->withHeader('Content-Type', 'application/json');
        $this->assertSame(
            Response::STATUS_CODE_406,
            $this->subject->process($request, $this->handler)->getStatusCode()
        );
    }

    public function testFormatAcceptRequest()
    {
        $this->assertIsArray(
            $this->subject->formatAcceptRequest('application/json')
        );
    }

    public function testCheckAccept()
    {
        $this->assertTrue(
            $this->subject->checkAccept(
                self::ROUTE_NAME,
                ['*/*']
            )
        );
        $this->assertTrue(
            $this->subject->checkAccept(
                self::ROUTE_NAME,
                ['application/json']
            )
        );
        $this->assertFalse(
            $this->subject->checkAccept(self::ROUTE_NAME, ['text/html'])
        );
    }

    public function testCheckContentType()
    {
        $this->assertTrue(
            $this->subject->checkContentType(self::ROUTE_NAME, '')
        );

        $this->assertTrue(
            $this->subject->checkContentType(
                self::ROUTE_NAME,
                'application/json'
            )
        );
        $this->assertFalse(
            $this->subject->checkContentType(self::ROUTE_NAME, 'text/html')
        );
    }
}
