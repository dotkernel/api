<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Entity\Admin;
use Api\Admin\InputFilter\UpdateAdminInputFilter;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminAccountHandler extends AbstractHandler
{
    #[Inject(
        AdminServiceInterface::class,
    )]
    public function __construct(
        protected AdminServiceInterface $adminService,
    ) {
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $request->getAttribute(Admin::class));
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateAdminInputFilter())->setData((array) $request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        $admin = $this->adminService->updateAdmin($request->getAttribute(Admin::class), $inputFilter->getValues());

        return $this->createResponse($request, $admin);
    }
}
