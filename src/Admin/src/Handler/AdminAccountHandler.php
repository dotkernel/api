<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Entity\Admin;
use Api\Admin\Form\InputFilter\UpdateAdminInputFilter;
use Api\Admin\Service\AdminService;
use Api\App\Handler\DefaultHandler;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class AdminAccountHandler
 * @package Api\Admin\Handler
 */
class AdminAccountHandler extends DefaultHandler
{
    protected AdminService $adminService;

    /**
     * AdminAccountHandler constructor.
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
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $request->getAttribute(Admin::class));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateAdminInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $admin = $this->adminService->updateAdmin(
                $request->getAttribute(Admin::class),
                $inputFilter->getValues()
            );
            return $this->createResponse($request, $admin);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
