<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\ExpiredException;
use Api\App\Exception\ForbiddenException;
use Api\App\Exception\MethodNotAllowedException;
use Api\App\Exception\NotFoundException;
use Api\App\Exception\RuntimeException;
use Api\App\Exception\UnauthorizedException;
use Dot\DependencyInjection\Attribute\Inject;
use Dot\Mail\Exception\MailException;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator\Exception\OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_array;

class ResponseMiddleware implements MiddlewareInterface
{
    #[Inject(
        HalResponseFactory::class,
    )]
    public function __construct(
        protected HalResponseFactory $responseFactory,
    ) {
    }

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
