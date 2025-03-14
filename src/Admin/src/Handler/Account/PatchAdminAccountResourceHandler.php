<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Account;

use Api\Admin\InputFilter\UpdateAdminInputFilter;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Core\Admin\Entity\Admin;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchAdminAccountResourceHandler extends AbstractHandler
{
    #[Inject(
        AdminServiceInterface::class,
        UpdateAdminInputFilter::class,
    )]
    public function __construct(
        protected AdminServiceInterface $adminService,
        protected UpdateAdminInputFilter $inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($this->inputFilter->getMessages());
        }

        $admin = $request->getAttribute(Admin::class);
        $this->adminService->updateAdmin($admin, (array) $this->inputFilter->getValues());

        return $this->createResponse($request, $admin);
    }
}
