<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Collection\CollectionInterface;
use Api\App\Entity\EntityInterface;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\ExpiredException;
use Api\App\Exception\ForbiddenException;
use Api\App\Exception\MethodNotAllowedException;
use Api\App\Exception\NotFoundException;
use Api\App\Exception\RuntimeException;
use Api\App\Exception\UnauthorizedException;
use Api\App\Message;
use Dot\Mail\Exception\MailException;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Mezzio\Hal\ResourceGenerator\Exception\OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;
use function is_array;
use function method_exists;
use function strtolower;

abstract class AbstractHandler implements RequestHandlerInterface
{
    protected ?HalResponseFactory $responseFactory  = null;
    protected ?ResourceGenerator $resourceGenerator = null;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $method = strtolower($request->getMethod());
            if (! method_exists($this, $method)) {
                throw new MethodNotAllowedException(Message::METHOD_NOT_ALLOWED);
            }

            return $this->$method($request);
        } catch (ConflictException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_CONFLICT);
        } catch (ForbiddenException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_FORBIDDEN);
        } catch (ExpiredException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_GONE);
        } catch (OutOfBoundsException | NotFoundException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_NOT_FOUND);
        } catch (UnauthorizedException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_UNAUTHORIZED);
        } catch (MethodNotAllowedException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED);
        } catch (BadRequestException $exception) {
            return $this->errorResponse($exception->getMessages(), StatusCodeInterface::STATUS_BAD_REQUEST);
        } catch (MailException | RuntimeException | Exception $exception) {
            return $this->errorResponse($exception->getMessage());
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
}
