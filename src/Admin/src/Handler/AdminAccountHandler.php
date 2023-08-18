<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Entity\Admin;
use Api\Admin\InputFilter\UpdateAdminInputFilter;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\Handler\ResponseTrait;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class AdminAccountHandler implements RequestHandlerInterface
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
    ) {
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse(
            $request,
            $request->getAttribute(Admin::class)
        );
    }

    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateAdminInputFilter())->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
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
