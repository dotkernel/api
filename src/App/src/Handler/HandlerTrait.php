<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\ExpiredException;
use Api\App\Exception\ForbiddenException;
use Api\App\Exception\MethodNotAllowedException;
use Api\App\Exception\NotFoundException;
use Api\App\Exception\UnauthorizedException;
use Dot\Mail\Exception\MailException;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\Hal\ResourceGenerator\Exception\OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

use function method_exists;
use function sprintf;
use function strtolower;

trait HandlerTrait
{
    use ResponseTrait;

    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $method = strtolower($request->getMethod());
            if (! method_exists($this, $method)) {
                throw new MethodNotAllowedException(
                    sprintf('Method %s is not implemented for the requested resource.', $method)
                );
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
}
