<?php

declare(strict_types=1);

namespace Api\Admin\Handler;

use Api\Admin\Entity\Admin;
use Api\Admin\InputFilter\CreateAdminInputFilter;
use Api\Admin\InputFilter\UpdateAdminInputFilter;
use Api\Admin\Service\AdminServiceInterface;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\HandlerTrait;
use Api\App\Message;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function sprintf;

class AdminHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     AdminServiceInterface::class,
     *     "config",
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected AdminServiceInterface $adminService,
        protected array $config,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');

        $admin = $this->adminService->findOneBy(['uuid' => $uuid]);
        if (! $admin instanceof Admin) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'admin', $uuid));
        }

        $this->adminService->deleteAdmin($admin);

        return $this->noContentResponse();
    }

    /**
     * @throws NotFoundException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');

        $admin = $this->adminService->findOneBy(['uuid' => $uuid]);
        if (! $admin instanceof Admin) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'admin', $uuid));
        }

        return $this->createResponse($request, $admin);
    }

    public function getCollection(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $this->adminService->getAdmins($request->getQueryParams()));
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

        $uuid  = $request->getAttribute('uuid');
        $admin = $this->adminService->findOneBy(['uuid' => $uuid]);
        if (! $admin instanceof Admin) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'admin', $uuid));
        }

        $user = $this->adminService->updateAdmin($admin, $inputFilter->getValues());

        return $this->createResponse($request, $user);
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new CreateAdminInputFilter())->setData((array) $request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        $admin = $this->adminService->createAdmin($inputFilter->getValues());

        return $this->createdResponse($request, $admin);
    }
}
