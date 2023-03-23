<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Entity\Admin;
use Api\Admin\InputFilter\CreateAdminInputFilter;
use Api\Admin\InputFilter\UpdateAdminInputFilter;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\Handler\ResponseTrait;
use Api\App\Message;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class AdminHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     AdminServiceInterface::class
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected AdminServiceInterface $adminService
    ) {}

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            $admin = $this->adminService->findOneBy(['uuid' => $uuid]);
            if (!$admin instanceof Admin) {
                return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'admin', $uuid));
            }

            $this->adminService->deleteAdmin($admin);

            return $this->infoResponse(Message::ADMIN_DELETED);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

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

    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateAdminInputFilter())->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $uuid = $request->getAttribute('uuid');
            $admin = $this->adminService->findOneBy(['uuid' => $uuid]);
            if (!$admin instanceof Admin) {
                return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'admin', $uuid));
            }

            $user = $this->adminService->updateAdmin($admin, $inputFilter->getValues());

            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new CreateAdminInputFilter())->setData($request->getParsedBody());
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

    private function list(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $this->adminService->getAdmins($request->getQueryParams()));
    }

    private function view(ServerRequestInterface $request, string $uuid): ResponseInterface
    {
        $admin = $this->adminService->findOneBy(['uuid' => $uuid]);
        if (!$admin instanceof Admin) {
            return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'admin', $uuid));
        }

        return $this->createResponse($request, $admin);
    }
}
