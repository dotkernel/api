<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Exception\ConflictException;
use Api\App\Exception\ExpiredException;
use Api\App\Exception\FormValidationException;
use Api\App\Exception\MethodNotAllowedException;
use Api\App\Exception\NotFoundException;
use Dot\Mail\Exception\MailException;
use Exception;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Hal\Metadata\MetadataMap;
use Mezzio\Hal\Metadata\RouteBasedCollectionMetadata;
use Mezzio\Hal\ResourceGenerator\Exception\OutOfBoundsException;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

use function array_key_exists;
use function is_array;
use function method_exists;
use function sprintf;
use function strtolower;

trait ResponseTrait
{
    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $method = strtolower($request->getMethod());
            if ($request->getMethod() === RequestMethodInterface::METHOD_GET) {
                /** @var RouteResult $routeResult */
                $routeResult = $request->getAttribute(RouteResult::class);
                $halConfig   = $this->getHalConfig($routeResult->getMatchedRouteName());
                if (empty($halConfig)) {
                    throw new NotFoundException(
                        sprintf('Unable to identify HAL config for route: %s', $routeResult->getMatchedRouteName())
                    );
                }
                if ($this->isCollection($halConfig)) {
                    $method = 'getCollection';
                }
            }

            if (method_exists($this, $method)) {
                return $this->$method($request);
            }
            throw new MethodNotAllowedException(
                sprintf('Method %s is not implemented for the requested resource.', $method)
            );
        } catch (ConflictException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_CONFLICT);
        } catch (ExpiredException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_GONE);
        } catch (OutOfBoundsException | NotFoundException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_NOT_FOUND);
        } catch (MethodNotAllowedException $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED);
        } catch (FormValidationException $exception) {
            return $this->errorResponse($exception->getMessages());
        } catch (MailException | RuntimeException | Exception $exception) {
            return $this->errorResponse($exception->getMessage(), StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

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
        int $status = StatusCodeInterface::STATUS_BAD_REQUEST
    ): ResponseInterface {
        return $this->restResponse([
            'error' => [
                'messages' => is_array($messages) ? $messages : [$messages],
            ],
        ], $status);
    }

    public function infoResponse(
        array|string $messages = [],
        int $status = StatusCodeInterface::STATUS_OK
    ): ResponseInterface {
        return $this->restResponse([
            'info' => [
                'messages' => is_array($messages) ? $messages : [$messages],
            ],
        ], $status);
    }

    public function redirectResponse(string $location): ResponseInterface
    {
        return new RedirectResponse($location);
    }

    public function restResponse(
        array|string $messages = [],
        int $status = StatusCodeInterface::STATUS_OK
    ): ResponseInterface {
        return new JsonResponse($messages, $status);
    }

    private function getHalConfig(string $routeName): ?array
    {
        foreach ($this->config[MetadataMap::class] ?? [] as $config) {
            if ($config['route'] === $routeName) {
                return $config;
            }
        }

        return null;
    }

    private function isCollection(array $halConfig): bool
    {
        return array_key_exists('__class__', $halConfig)
            && $halConfig['__class__'] === RouteBasedCollectionMetadata::class;
    }
}
