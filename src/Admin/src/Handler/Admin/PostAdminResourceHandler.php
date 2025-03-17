<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin;

use Api\Admin\InputFilter\CreateAdminInputFilter;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostAdminResourceHandler extends AbstractHandler
{
    #[Inject(
        AdminServiceInterface::class,
        CreateAdminInputFilter::class,
    )]
    public function __construct(
        protected AdminServiceInterface $adminService,
        protected CreateAdminInputFilter $inputFilter,
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

        $admin = $this->adminService->createAdmin((array) $this->inputFilter->getValues());

        return $this->createdResponse($request, $admin);
    }
}
