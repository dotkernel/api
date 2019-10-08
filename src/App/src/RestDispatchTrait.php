<?php
declare(strict_types=1);

namespace Api\App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;
use Zend\Expressive\Hal\ResourceGenerator\Exception\OutOfBoundsException;
use Zend\Http\Response;

/**
 * Trait RestDispatchTrait
 * @package App
 */
trait RestDispatchTrait
{
    /**
     * @var ResourceGenerator
     */
    private $resourceGenerator;

    /**
     * @var HalResponseFactory
     */
    private $responseFactory;

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $method = strtolower($request->getMethod());
        if (method_exists($this, $method)) {
            return $this->$method($request);
        }
        throw Exception\MethodNotAllowedException::create(sprintf(
            'Method %s is not implemented for the requested resource',
            strtoupper($method)
        ));
    }

    /**
     * @param ServerRequestInterface $request
     * @param $instance
     * @return ResponseInterface
     */
    private function createResponse(ServerRequestInterface $request, $instance): ResponseInterface
    {
        try {
            return $this->responseFactory->createResponse(
                $request,
                $this->resourceGenerator->fromObject($instance, $request)
            );
        } catch (OutOfBoundsException $e) {
            throw Exception\OutOfBoundsException::create($e->getMessage());
        }
    }

    /**
     * @param array|string $messages
     * @param int $status
     * @return JsonResponse
     */
    public function errorResponse($messages = [], int $status = Response::STATUS_CODE_400)
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
     * @return JsonResponse
     */
    public function notFoundResponse($messages = [])
    {
        return $this->errorResponse($messages, Response::STATUS_CODE_404);
    }

    /**
     * @param array|string $messages
     * @param int $status
     * @return JsonResponse
     */
    public function restResponse($messages = [], int $status = Response::STATUS_CODE_200)
    {
        return new JsonResponse($messages, $status);
    }
}
