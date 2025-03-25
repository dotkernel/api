<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin;

use Api\Admin\InputFilter\UpdateAdminInputFilter;
use Api\App\Handler\AbstractHandler;
use Core\Admin\Service\AdminServiceInterface;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchAdminResourceHandler extends AbstractHandler
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

        $admin = $this->adminService->find($request->getAttribute('uuid'));

        $this->adminService->updateAdmin($admin, (array) $this->inputFilter->getValues());

        return $this->createResponse($request, $admin);
    }
}
