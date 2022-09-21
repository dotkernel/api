<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Exception\MethodNotAllowedException;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Http\Response;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Mezzio\Hal\ResourceGenerator\Exception\OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class DefaultHandler
 * @package Api\App\Handler
 */
class DefaultHandler implements RequestHandlerInterface
{
    private ResourceGenerator $resourceGenerator;

    private HalResponseFactory $responseFactory;

    /**
     * AccountActivateHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     */
    public function __construct(HalResponseFactory $halResponseFactory, ResourceGenerator $resourceGenerator)
    {
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = strtolower($request->getMethod());
        if (method_exists($this, $method)) {
            return $this->$method($request);
        }
        throw MethodNotAllowedException::create(sprintf(
            'Method %s is not implemented for the requested resource',
            strtoupper($method)
        ));
    }

    /**
     * @param ServerRequestInterface $request
     * @param mixed $instance
     *
     * @return ResponseInterface
     */
    public function createResponse(ServerRequestInterface $request, $instance): ResponseInterface
    {
        try {
            return $this->responseFactory->createResponse(
                $request,
                $this->resourceGenerator->fromObject($instance, $request)
            );
        } catch (OutOfBoundsException $e) {
            throw \Api\App\Exception\OutOfBoundsException::create($e->getMessage());
        }
    }

    /**
     * @param array|string $messages
     * @param int $status
     * @return ResponseInterface
     */
    public function errorResponse($messages = [], int $status = Response::STATUS_CODE_400): ResponseInterface
    {
        if (!empty($messages) && !is_array($messages)) {
            $messages = [$messages];
        }

        return $this->restResponse([
            'error' => [
                'messages' => $messages
            ]
        ], $status);
    }

    /**
     * @param array|string $messages
     * @param int $status
     * @return ResponseInterface
     */
    public function infoResponse($messages = [], int $status = Response::STATUS_CODE_200): ResponseInterface
    {
        if (!empty($messages) && !is_array($messages)) {
            $messages = [$messages];
        }

        return $this->restResponse([
            'info' => [
                'messages' => $messages
            ]
        ], $status);
    }

    /**
     * @param array|string $messages
     * @return ResponseInterface
     */
    public function notFoundResponse($messages = []): ResponseInterface
    {
        return $this->errorResponse($messages, Response::STATUS_CODE_404);
    }

    /**
     * @param string $location
     * @return ResponseInterface
     */
    public function redirectResponse(string $location): ResponseInterface
    {
        return new RedirectResponse($location);
    }

    /**
     * @param array|string $messages
     * @param int $status
     * @return ResponseInterface
     */
    public function restResponse($messages = [], int $status = Response::STATUS_CODE_200): ResponseInterface
    {
        return new JsonResponse($messages, $status);
    }
}
