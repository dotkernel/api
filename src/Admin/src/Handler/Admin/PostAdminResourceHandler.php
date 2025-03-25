<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Admin;

use Api\Admin\InputFilter\CreateAdminInputFilter;
use Api\App\Handler\AbstractHandler;
use Core\Admin\Service\AdminServiceInterface;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
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

        return $this->createdResponse(
            $request,
            $this->adminService->createAdmin((array) $this->inputFilter->getValues())
        );
    }
}
