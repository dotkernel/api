<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Entity\AdminRole;
use Api\Admin\Service\AdminRoleServiceInterface;
use Api\App\Handler\ResponseTrait;
use Api\App\Message;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function sprintf;

class AdminRoleHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     AdminRoleServiceInterface::class
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected AdminRoleServiceInterface $roleService
    ) {
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            if (! empty($uuid)) {
                return $this->view($request, $uuid);
            }

            return $this->list($request);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    private function list(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $this->roleService->getAdminRoles($request->getQueryParams())
        );
    }

    private function view(ServerRequestInterface $request, string $uuid): ResponseInterface
    {
        $role = $this->roleService->findOneBy(['uuid' => $uuid]);
        if (! $role instanceof AdminRole) {
            return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'role', $uuid));
        }

        return $this->createResponse($request, $role);
    }
}
