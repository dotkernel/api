<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\NotFoundException;
use Api\App\Handler\HandlerTrait;
use Api\User\Service\UserRoleServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserRoleHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    #[Inject(
        HalResponseFactory::class,
        ResourceGenerator::class,
        UserRoleServiceInterface::class,
    )]
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserRoleServiceInterface $roleService,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $role = $this->roleService->findOneBy(['uuid' => $request->getAttribute('uuid')]);

        return $this->createResponse($request, $role);
    }
}
