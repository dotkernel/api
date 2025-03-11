<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Collection\CollectionInterface;
use Core\App\Entity\EntityInterface;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;
use function is_array;

abstract class AbstractHandler implements RequestHandlerInterface
{
    protected ?HalResponseFactory $responseFactory  = null;
    protected ?ResourceGenerator $resourceGenerator = null;

    public function setResponseFactory(HalResponseFactory $responseFactory): self
    {
        $this->responseFactory = $responseFactory;

        return $this;
    }

    public function setResourceGenerator(ResourceGenerator $resourceGenerator): self
    {
        $this->resourceGenerator = $resourceGenerator;

        return $this;
    }

    public function createResponse(
        ServerRequestInterface $request,
        CollectionInterface|EntityInterface $instance
    ): ResponseInterface {
        assert($this->responseFactory instanceof HalResponseFactory);
        assert($this->resourceGenerator instanceof ResourceGenerator);

        return $this->responseFactory->createResponse(
            $request,
            $this->resourceGenerator->fromObject($instance, $request)
        );
    }

    public function createdResponse(ServerRequestInterface $request, EntityInterface $instance): ResponseInterface
    {
        $response = $this->createResponse($request, $instance);

        return $response->withStatus(StatusCodeInterface::STATUS_CREATED);
    }

    public function emptyResponse(int $status = StatusCodeInterface::STATUS_NO_CONTENT): ResponseInterface
    {
        return new EmptyResponse($status, ['Content-Type' => 'text/plain']);
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

    public function noContentResponse(): ResponseInterface
    {
        return $this->emptyResponse();
    }

    public function notFoundResponse(): ResponseInterface
    {
        return $this->emptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
    }
}
