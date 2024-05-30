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
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
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
            if ($this->isGetCollectionRequest($request, $this->config)) {
                $method = 'getCollection';
            }

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

    /**
     * @throws RuntimeException
     */
    private function isGetCollectionRequest(ServerRequestInterface $request, array $config): bool
    {
        if ($request->getMethod() !== RequestMethodInterface::METHOD_GET) {
            return false;
        }

        if (! array_key_exists(MetadataMap::class, $config)) {
            throw new RuntimeException(
                sprintf('Unable to load %s from config.', MetadataMap::class)
            );
        }

        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        $routeName   = $routeResult->getMatchedRouteName();

        $halConfig = null;
        foreach ($config[MetadataMap::class] as $cfg) {
            if ($cfg['route'] === $routeName) {
                $halConfig = $cfg;
                break;
            }
        }

        return is_array($halConfig)
            && array_key_exists('__class__', $halConfig)
            && $halConfig['__class__'] === RouteBasedCollectionMetadata::class;
    }
}
