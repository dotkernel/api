<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function is_array;

trait ResponseTrait
{
    public function createResponse(ServerRequestInterface $request, mixed $instance): ResponseInterface
    {
        return $this->responseFactory->createResponse(
            $request,
            $this->resourceGenerator->fromObject($instance, $request)
        );
    }

    public function createdResponse(ServerRequestInterface $request, mixed $instance): ResponseInterface
    {
        $response = $this->createResponse($request, $instance);

        return $response->withStatus(StatusCodeInterface::STATUS_CREATED);
    }

    public function notFoundResponse(): ResponseInterface
    {
        return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND, ['Content-Type' => 'text/plain']);
    }

    public function errorResponse(
        array|string $messages = [],
        int $status = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
    ): ResponseInterface {
        return $this->jsonResponse([
            'error' => [
                'messages' => is_array($messages) ? $messages : [$messages],
            ],
        ], $status);
    }

    public function infoResponse(
        array|string $messages = [],
        int $status = StatusCodeInterface::STATUS_OK
    ): ResponseInterface {
        return $this->jsonResponse([
            'info' => [
                'messages' => is_array($messages) ? $messages : [$messages],
            ],
        ], $status);
    }

    public function jsonResponse(
        array|string $messages = [],
        int $status = StatusCodeInterface::STATUS_OK
    ): ResponseInterface {
        return new JsonResponse($messages, $status);
    }

    public function notAcceptedResponse(string $message): ResponseInterface
    {
        return $this->errorResponse($message, StatusCodeInterface::STATUS_NOT_ACCEPTABLE);
    }

    public function unsupportedMediaTypeResponse(string $message): ResponseInterface
    {
        return $this->errorResponse($message, StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE);
    }
}
