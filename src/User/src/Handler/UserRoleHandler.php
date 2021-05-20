<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Message;
use Api\App\Handler\DefaultHandler;
use Api\User\Entity\UserRole;
use Api\User\Service\UserRoleService;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class UserRoleHandler
 * @package Api\User\Handler
 */
class UserRoleHandler extends DefaultHandler
{
    protected UserRoleService $roleService;

    /**
     * UserHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param UserRoleService $roleService
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, UserRoleService::class})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        UserRoleService $roleService)
    {
        parent::__construct($halResponseFactory, $resourceGenerator);

        $this->roleService = $roleService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            if (!is_null($uuid)) {
                $role = $this->roleService->findOneBy(['uuid' => $uuid]);
                if (!($role instanceof UserRole)) {
                    return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'role', $uuid));
                }
                return $this->createResponse($request, $role);
            } else {
                return $this->createResponse($request, $this->roleService->getRoles($request->getQueryParams()));
            }
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
