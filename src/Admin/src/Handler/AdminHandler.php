<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Entity\Admin;
use Api\Admin\InputFilter\CreateAdminInputFilter;
use Api\Admin\InputFilter\UpdateAdminInputFilter;
use Api\Admin\Service\AdminService;
use Dot\AnnotatedServices\Annotation\Inject;
use Api\App\Handler\DefaultHandler;
use Api\App\Message;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class AdminHandler
 * @package Api\Admin\Handler
 */
class AdminHandler extends DefaultHandler
{
    protected AdminService $adminService;

    /**
     * AdminHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param AdminService $adminService
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, AdminService::class})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        AdminService $adminService
    ) {
        parent::__construct($halResponseFactory, $resourceGenerator);

        $this->adminService = $adminService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            $admin = $this->adminService->findOneBy(['uuid' => $uuid]);
            if (!($admin instanceof Admin)) {
                return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'admin', $uuid));
            }
            $this->adminService->deleteAdmin($admin);
            return $this->infoResponse(Message::ADMIN_DELETED);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
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
                $admin = $this->adminService->findOneBy(['uuid' => $uuid]);
                if (!($admin instanceof Admin)) {
                    return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'admin', $uuid));
                }
                return $this->createResponse($request, $admin);
            } else {
                return $this->createResponse($request, $this->adminService->getAdmins($request->getQueryParams()));
            }
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    /**
     * Update user by uuid
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = new UpdateAdminInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $uuid = $request->getAttribute('uuid');
            $admin = $this->adminService->findOneBy(['uuid' => $uuid]);
            if (!($admin instanceof Admin)) {
                return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'admin', $uuid));
            }
            $user = $this->adminService->updateAdmin($admin, $inputFilter->getValues());
            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    /**
     * Create user
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = new CreateAdminInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $admin = $this->adminService->createAdmin($inputFilter->getValues());
            return $this->createResponse($request, $admin);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
