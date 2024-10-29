<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Dot\DependencyInjection\Attribute\Inject;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_filter;
use function array_intersect;
use function array_map;
use function explode;
use function in_array;
use function is_array;
use function str_contains;
use function strtok;
use function trim;

readonly class ContentNegotiationMiddleware implements MiddlewareInterface
{
    #[Inject(
        "config.content-negotiation",
    )]
    public function __construct(
        private array $config,
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $routeResult = $request->getAttribute(RouteResult::class);
        if (! $routeResult instanceof RouteResult || $routeResult->isFailure()) {
            return $handler->handle($request);
        }

        $routeName = (string) $routeResult->getMatchedRouteName();

        $accept = $this->formatAcceptRequest($request->getHeaderLine('Accept'));
        if (! $this->checkAccept($routeName, $accept)) {
            return $this->notAcceptableResponse('Not Acceptable');
        }

        $contentType = $request->getHeaderLine('Content-Type');
        if (! $this->checkContentType($routeName, $contentType)) {
            return $this->unsupportedMediaTypeResponse('Unsupported Media Type');
        }

        $response = $handler->handle($request);

        $responseContentType = $response->getHeaderLine('Content-Type');

        if (! $this->validateResponseContentType($responseContentType, $accept)) {
            return $this->notAcceptableResponse('Unable to resolve Accept header to a representation');
        }

        return $response;
    }

    public function formatAcceptRequest(string $accept): array
    {
        $accept = array_map(function ($item) {
            return trim((string) strtok($item, ';'));
        }, explode(',', $accept));

        return array_filter($accept);
    }

    public function checkAccept(string $routeName, array $accept): bool
    {
        if (in_array('*/*', $accept, true)) {
            return true;
        }

        $acceptList = $this->config['default']['Accept'] ?? [];
        if (! empty($this->config[$routeName]['Accept'])) {
            $acceptList = $this->config[$routeName]['Accept'] ?? [];
        }

        if (is_array($acceptList)) {
            return ! empty(array_intersect($accept, $acceptList));
        } else {
            return in_array($acceptList, $accept, true);
        }
    }

    public function checkContentType(string $routeName, string $contentType): bool
    {
        if (empty($contentType)) {
            return true;
        }

        $contentType = explode(';', $contentType);

        $acceptList = $this->config['default']['Content-Type'] ?? [];
        if (! empty($this->config[$routeName]['Content-Type'])) {
            $acceptList = $this->config[$routeName]['Content-Type'] ?? [];
        }

        if (is_array($acceptList)) {
            return ! empty(array_intersect($contentType, $acceptList));
        } else {
            return in_array($acceptList, $contentType, true);
        }
    }

    public function validateResponseContentType(?string $contentType, array $accept): bool
    {
        if (in_array('*/*', $accept, true)) {
            return true;
        }

        if (null === $contentType) {
            return false;
        }

        $accept = array_map(function ($item) {
            return str_contains($item, 'json') ? 'json' : $item;
        }, $accept);

        if (str_contains($contentType, 'json')) {
            $contentType = 'json';
        }

        return in_array($contentType, $accept, true);
    }

    public function notAcceptableResponse(string $message): ResponseInterface
    {
        return new JsonResponse(['messages' => [$message]], StatusCodeInterface::STATUS_NOT_ACCEPTABLE);
    }

    public function unsupportedMediaTypeResponse(string $message): ResponseInterface
    {
        return new JsonResponse(['messages' => [$message]], StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE);
    }
}
