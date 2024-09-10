<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Service\AdminRoleServiceInterface;
use Api\App\Exception\BadRequestException;
use Api\App\Handler\HandlerTrait;
use Dot\DependencyInjection\Attribute\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AdminRoleCollectionHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    #[Inject(
        HalResponseFactory::class,
        ResourceGenerator::class,
        AdminRoleServiceInterface::class,
    )]
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected AdminRoleServiceInterface $roleService,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $this->roleService->getAdminRoles($request->getQueryParams()));
    }
}
