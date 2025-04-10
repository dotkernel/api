<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Account;

use Api\Admin\InputFilter\UpdateAdminInputFilter;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\Handler\AbstractHandler;
use Core\Admin\Entity\Admin;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
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

        return $this->createResponse(
            $request,
            $this->adminService->saveAdmin(
                (array) $this->inputFilter->getValues(),
                $request->getAttribute(Admin::class)
            )
        );
    }
}
