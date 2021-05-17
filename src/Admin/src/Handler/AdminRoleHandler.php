<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Entity\AdminRole;
use Api\Admin\Service\AdminRoleService;
use Dot\AnnotatedServices\Annotation\Inject;
use Api\App\Handler\DefaultHandler;
use Api\App\Message;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class AdminRoleHandler
 * @package Api\Admin\Handler
 */
class AdminRoleHandler extends DefaultHandler
{
    protected AdminRoleService $roleService;

    /**
     * AdminRoleHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param AdminRoleService $roleService
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, AdminRoleService::class})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        AdminRoleService $roleService)
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
                if (!($role instanceof AdminRole)) {
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
