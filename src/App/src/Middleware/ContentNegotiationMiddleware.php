<?php

namespace Api\App\Middleware;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Http\Response;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Dot\AnnotatedServices\Annotation\Inject;

class ContentNegotiationMiddleware implements MiddlewareInterface
{

    /**
     * @Inject({"config.content-negotiation"})
     */
    public function __construct(private array $config)
    {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        if (!$routeResult instanceof RouteResult || $routeResult->isFailure()) {
            return $handler->handle($request);
        }

        $routeName = $routeResult->getMatchedRoute()->getName();

        $accept = $this->formatAcceptRequest($request->getHeaderLine('Accept'));
        if (!$this->checkAccept($routeName, $accept)){
            return $this->notAcceptedResponse('Not Acceptable');
        }

        $contentType = $request->getHeaderLine('Content-Type');
        if (!$this->checkContentType($routeName, $contentType)) {
            return $this->unsupportedMediaTypeResponse(
                'Unsupported Media Type'
            );
        }

        $response = $handler->handle($request);

        $responseContentType = $response->getHeaderLine('Content-Type');
        if (!$this->validateResponseContentType(
            $responseContentType,
            $accept
        )
        ) {
            return $this->notAcceptedResponse(
                'Unable to resolve Accept header to a representation'
            );
        }

        return $response;
    }

    private function formatAcceptRequest($accept): array
    {
        $accept = array_map(function ($item) {
            return trim(strtok($item, ';'));
        }, explode(',', $accept));

        return array_filter($accept);
    }

    private function checkAccept(string $routeName, array $accept): bool
    {
        if (in_array('*/*', $accept, true)) {
            return true;
        }

        $acceptList = $this->config['default']['Accept'] ?? [];
        if (isset($this->config[$routeName])) {
            $acceptList = $this->config[$routeName]['Accept'] ?? [];
        }

        if (is_array($acceptList)) {
            return !empty(array_intersect($accept, $acceptList));
        } else {
            return in_array($acceptList, $accept, true);
        }
    }

    private function checkContentType(
        string $routeName,
        string $contentType
    ): bool {
        if (empty($contentType)) {
            return true;
        }
        $acceptList = $this->config['default']['Content-Type'] ?? [];
        if (isset($this->config[$routeName])) {
            $acceptList = $this->config[$routeName]['Content-Type'] ?? [];
        }

        if (is_array($acceptList)) {
            return in_array($contentType, $acceptList, true);
        } else {
            return $contentType === $acceptList;
        }
    }

    private function notAcceptedResponse(string $message): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'messages' => [
                    $message,
                ],
            ],
        ], Response::STATUS_CODE_406);
    }

    private function unsupportedMediaTypeResponse(string $message): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'messages' => [
                    $message,
                ],
            ],
        ], Response::STATUS_CODE_415);
    }

    private function validateResponseContentType(string $contentType, array $accept): bool
    {
        if (in_array('*/*', $accept, true)) {
            return true;
        }

        $accept = array_map(function ($item) {
            return str_contains($item, 'json') ? 'json' : $item;
        }, $accept);

        if (str_contains($contentType, 'json')) {
            $contentType = 'json';
        }

        return in_array($contentType, $accept, true);
    }
}
