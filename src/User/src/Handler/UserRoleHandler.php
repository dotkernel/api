<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Handler\ResponseTrait;
use Api\App\Message;
use Api\User\Entity\UserRole;
use Api\User\Service\UserRoleServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class UserRoleHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     UserRoleServiceInterface::class
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserRoleServiceInterface $roleService
    ) {}

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            if (!empty($uuid)) {
                return $this->view($request, $uuid);
            }

            return $this->list($request);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    private function list(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $this->roleService->getRoles($request->getQueryParams()));
    }

    private function view(ServerRequestInterface $request, string $uuid): ResponseInterface
    {
        $role = $this->roleService->findOneBy(['uuid' => $uuid]);
        if (!($role instanceof UserRole)) {
            return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'role', $uuid));
        }

        return $this->createResponse($request, $role);
    }
}
