<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Collection\CollectionInterface;
use Api\App\Entity\EntityInterface;
use Api\App\Exception\MethodNotAllowedException;
use Api\App\Message;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Mezzio\ProblemDetails\ProblemDetailsResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function assert;
use function is_array;
use function method_exists;
use function strtolower;

abstract class AbstractHandler implements RequestHandlerInterface
{
    protected ?HalResponseFactory $responseFactory                  = null;
    protected ?ResourceGenerator $resourceGenerator                 = null;
    protected ?ProblemDetailsResponseFactory $problemDetailsFactory = null;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $method = strtolower($request->getMethod());
            if (! method_exists($this, $method)) {
                throw new MethodNotAllowedException(Message::METHOD_NOT_ALLOWED);
            }

            return $this->$method($request);
        } catch (Throwable $exception) {
            assert($this->problemDetailsFactory instanceof ProblemDetailsResponseFactory);
            return $this->problemDetailsFactory->createResponseFromThrowable($request, $exception);
        }
    }

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

    public function setProblemDetailsFactory(ProblemDetailsResponseFactory $problemDetailsFactory): self
    {
        $this->problemDetailsFactory = $problemDetailsFactory;

        return $this;
    }

    public function emptyResponse(int $status = StatusCodeInterface::STATUS_NO_CONTENT): ResponseInterface
    {
        return new EmptyResponse($status, ['Content-Type' => 'text/plain']);
    }

    public function jsonResponse(
        array|string $messages = [],
        int $status = StatusCodeInterface::STATUS_OK
    ): ResponseInterface {
        return new JsonResponse($messages, $status);
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

    public function noContentResponse(): ResponseInterface
    {
        return $this->emptyResponse();
    }

    public function notFoundResponse(): ResponseInterface
    {
        return $this->emptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
    }

    public function infoResponse(
        array|string $messages = [],
        int $status = StatusCodeInterface::STATUS_OK
    ): ResponseInterface {
        return $this->jsonResponse([
            'messages' => is_array($messages) ? $messages : [$messages],
        ], $status);
    }
}
