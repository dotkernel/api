<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Entity\AdminRole;
use Api\Admin\Service\AdminRoleServiceInterface;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\ResponseTrait;
use Api\App\Message;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function sprintf;

class AdminRoleHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     AdminRoleServiceInterface::class,
     *     "config"
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected AdminRoleServiceInterface $roleService,
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
        if (! $role instanceof AdminRole) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'role', $uuid));
        }

        return $this->createResponse($request, $role);
    }

    public function getCollection(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $this->roleService->getAdminRoles($request->getQueryParams())
        );
    }
}
