<?php

declare(strict_types=1);

namespace Api\Admin\Handler\Account;

use Api\Admin\InputFilter\UpdateAdminInputFilter;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\App\IdentityInterface;
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
        $result = $this->inputFilter->validate((array) $request->getParsedBody());
        if (! $result->valid()) {
            throw BadRequestException::fromValidationResult($result);
        }

        /** @var non-empty-array<non-empty-string, mixed> $data */
        $data = $result->value();

        return $this->createResponse(
            $request,
            $this->adminService->saveAdmin($data, $request->getAttribute(IdentityInterface::class))
        );
    }
}
