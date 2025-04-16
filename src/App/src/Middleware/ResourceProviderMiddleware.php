<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\App\Attribute\Resource;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\IdentityInterface;
use Api\App\Service\HandlerService;
use Core\App\Entity\EntityInterface;
use Core\App\Message;
use Doctrine\ORM\EntityManagerInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionException;

use function array_pop;
use function assert;
use function count;
use function explode;

readonly class ResourceProviderMiddleware implements MiddlewareInterface
{
    #[Inject(
        EntityManagerInterface::class,
    )]
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $reflectionHandler = HandlerService::getReflectionHandler($request);
        if (! $reflectionHandler instanceof ReflectionClass) {
            return $handler->handle($request);
        }

        $reflectionAttributes = $reflectionHandler->getMethod('handle')->getAttributes(Resource::class);
        if (count($reflectionAttributes) === 0) {
            return $handler->handle($request);
        }

        /** @var Resource $resource */
        $resource = $reflectionAttributes[0]->newInstance();

        if ($resource->identifier === 'uuid' || $resource->identifier === 'id') {
            $entity = $this->entityManager->getRepository($resource->entity)->find(
                $request->getAttribute($resource->placeholder)
            );
        } else {
            $entity = $this->entityManager->getRepository($resource->entity)->findOneBy([
                $resource->identifier => $request->getAttribute($resource->placeholder),
            ]);
        }

        if ($entity === null) {
            $entity = explode('\\', $resource->entity);
            throw NotFoundException::create(
                Message::resourceNotFound(array_pop($entity))
            );
        }
        assert($entity instanceof EntityInterface);

        if ($entity->isDeleted()) {
            $entity = explode('\\', $resource->entity);
            throw NotFoundException::create(
                Message::resourceNotFound(array_pop($entity))
            );
        }

        if ($resource->hasGuard()) {
            (new $resource->guard())(
                $this->entityManager,
                $request->getAttribute(IdentityInterface::class),
                $entity
            );
        }

        if ($request->getAttribute($entity::class) !== null) {
            throw ConflictException::create(
                Message::resourceAlreadyRegistered($entity::class)
            );
        }

        return $handler->handle(
            $request->withAttribute($entity::class, $entity)
        );
    }
}
