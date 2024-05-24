<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Exception\ConflictException;
use Api\App\Exception\ExpiredException;
use Api\App\Exception\FormValidationException;
use Api\App\Exception\InvalidResetPasswordException;
use Api\App\Exception\NotAllowedException;
use Api\App\Exception\NotFoundException;
use Dot\Mail\Exception\MailException;
use Exception;
use Fig\Http\Message\RequestMethodInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Http\Response;
use Mezzio\Hal\ResourceGenerator\Exception\OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

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
        $method = strtolower($request->getMethod());
        if ($request->getMethod() === RequestMethodInterface::METHOD_GET) {
            $uuid = $request->getAttribute('uuid');
            $hash = $request->getAttribute('hash');
            if (empty($uuid) && empty($hash)) {
                $method = 'getCollection';
            }
        }

        try {
            if (method_exists($this, $method)) {
                return $this->$method($request);
            }
            throw new NotAllowedException(
                sprintf('Method %s is not implemented for the requested resource.', $method)
            );
        } catch (ConflictException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::STATUS_CODE_409);
        } catch (ExpiredException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::STATUS_CODE_410);
        } catch (InvalidResetPasswordException $exception) {
            return $this->errorResponse($exception->getMessage());
        } catch (MailException $exception) {
            return $this->errorResponse($exception->getMessage());
        } catch (NotFoundException $exception) {
            return $this->notFoundResponse($exception->getMessage());
        } catch (NotAllowedException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::STATUS_CODE_405);
        } catch (OutOfBoundsException $exception) {
            return $this->errorResponse($exception->getMessage());
        } catch (RuntimeException $exception) {
            return $this->errorResponse($exception->getMessage());
        } catch (FormValidationException $exception) {
            return $this->errorResponse($exception->getMessages());
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function createResponse(ServerRequestInterface $request, mixed $instance): ResponseInterface
    {
        return $this->responseFactory->createResponse(
            $request,
            $this->resourceGenerator->fromObject($instance, $request)
        );
    }

    public function errorResponse(
        array|string $messages = [],
        int $status = Response::STATUS_CODE_400
    ): ResponseInterface {
        return $this->restResponse([
            'error' => [
                'messages' => is_array($messages) ? $messages : [$messages],
            ],
        ], $status);
    }

    public function infoResponse(
        array|string $messages = [],
        int $status = Response::STATUS_CODE_200
    ): ResponseInterface {
        return $this->restResponse([
            'info' => [
                'messages' => is_array($messages) ? $messages : [$messages],
            ],
        ], $status);
    }

    public function notFoundResponse(array|string $messages = []): ResponseInterface
    {
        return $this->errorResponse($messages, Response::STATUS_CODE_404);
    }

    public function redirectResponse(string $location): ResponseInterface
    {
        return new RedirectResponse($location);
    }

    public function restResponse(
        array|string $messages = [],
        int $status = Response::STATUS_CODE_200
    ): ResponseInterface {
        return new JsonResponse($messages, $status);
    }
}
