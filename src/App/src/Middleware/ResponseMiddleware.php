<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\ExpiredException;
use Core\App\Exception\ForbiddenException;
use Core\App\Exception\MethodNotAllowedException;
use Core\App\Exception\NotFoundException;
use Core\App\Exception\RuntimeException;
use Core\App\Exception\UnauthorizedException;
use Dot\Mail\Exception\MailException;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Hal\ResourceGenerator\Exception\OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_array;

class ResponseMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
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

    public function errorResponse(
        array|string $messages = [],
        int $status = StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
    ): ResponseInterface {
        return new JsonResponse([
            'error' => [
                'messages' => is_array($messages) ? $messages : [$messages],
            ],
        ], $status);
    }
}
