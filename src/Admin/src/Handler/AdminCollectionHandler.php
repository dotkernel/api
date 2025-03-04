<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Service\AdminServiceInterface;
use Api\App\Exception\BadRequestException;
use Api\App\Handler\AbstractHandler;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminCollectionHandler extends AbstractHandler
{
    #[Inject(
        AdminServiceInterface::class,
    )]
    public function __construct(
        protected AdminServiceInterface $adminService,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $this->adminService->getAdmins($request->getQueryParams()));
    }
}
