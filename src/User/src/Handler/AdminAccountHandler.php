<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Dot\AnnotatedServices\Annotation\Inject;
use Api\App\RestDispatchTrait;
use Api\User\Entity\Admin;
use Api\User\Form\InputFilter\CreateAdminAccountInputFilter;
use Api\User\Form\InputFilter\UpdateAdminAccountInputFilter;
use Api\User\Service\AdminService;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AdminAccountHandler
 * @package Api\User\Handler
 */
class AdminAccountHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var AdminService $adminService */
    protected AdminService $adminService;

    /**
     * AccountHandler constructor.
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
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
        $this->adminService = $adminService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createResponse(
            $request,
            $this->resourceGenerator->fromObject($request->getAttribute(Admin::class, null), $request)
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateAdminAccountInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $admin = $this->adminService->updateAdmin(
                $request->getAttribute(Admin::class, null),
                $inputFilter->getValues()
            );
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse($request, $this->resourceGenerator->fromObject($admin, $request));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new CreateAdminAccountInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $admin = $this->adminService->createAdmin($inputFilter->getValues());
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse($request, $this->resourceGenerator->fromObject($admin, $request));
    }
}
