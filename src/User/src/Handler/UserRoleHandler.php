<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\NotFoundException;
use Api\App\Handler\HandlerTrait;
use Api\App\Message;
use Api\User\Entity\UserRole;
use Api\User\Service\UserRoleServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function sprintf;

class UserRoleHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     UserRoleServiceInterface::class,
     *     "config"
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserRoleServiceInterface $roleService,
        protected array $config,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');
        $role = $this->roleService->findOneBy(['uuid' => $uuid]);
        if (! $role instanceof UserRole) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'role', $uuid));
        }

        return $this->createResponse($request, $role);
    }

    public function getCollection(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $this->roleService->getRoles($request->getQueryParams()));
    }
}
