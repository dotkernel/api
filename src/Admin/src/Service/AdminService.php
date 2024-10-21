<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Entity\Admin;
use Api\Admin\Repository\AdminRepository;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Message;
use Dot\DependencyInjection\Attribute\Inject;

use function in_array;
use function sprintf;

class AdminService implements AdminServiceInterface
{
    #[Inject(
        AdminRoleService::class,
        AdminRepository::class,
    )]
    public function __construct(
        protected AdminRoleService $adminRoleService,
        protected AdminRepository $adminRepository,
    ) {
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function createAdmin(array $data = []): Admin
    {
        if ($this->exists($data['identity'])) {
            throw ConflictException::create(Message::DUPLICATE_IDENTITY);
        }

        $admin = (new Admin())
            ->setIdentity($data['identity'])
            ->usePassword($data['password'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setStatus($data['status'] ?? Admin::STATUS_ACTIVE);

        foreach ($data['roles'] as $roleData) {
            $admin->addRole(
                $this->adminRoleService->findOneBy(['uuid' => $roleData['uuid']])
            );
        }

        return $this->adminRepository->saveAdmin($admin);
    }

    public function deleteAdmin(Admin $admin): void
    {
        $this->adminRepository->deleteAdmin(
            $admin->resetRoles()->deactivate()
        );
    }

    public function exists(string $identity = ''): bool
    {
        try {
            return $this->findOneBy(['identity' => $identity]) instanceof Admin;
        } catch (NotFoundException) {
            return false;
        }
    }

    public function existsOther(string $identity = '', string $uuid = ''): bool
    {
        try {
            $admin = $this->findOneBy(['identity' => $identity]);

            return $admin->getUuid()->toString() !== $uuid;
        } catch (NotFoundException) {
            return false;
        }
    }

    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params = []): Admin
    {
        $admin = $this->adminRepository->findOneBy($params);
        if (! $admin instanceof Admin) {
            throw NotFoundException::create(Message::ADMIN_NOT_FOUND);
        }

        return $admin;
    }

    /**
     * @throws BadRequestException
     */
    public function getAdmins(array $params = []): AdminCollection
    {
        $orders = [
            'admin.uuid',
            'admin.identity',
            'admin.firstName',
            'admin.lastName',
            'admin.status',
            'admin.created',
            'admin.updated',
        ];

        $params['order'] = $params['order'] ?? 'admin.created';
        if (! in_array($params['order'], $orders)) {
            throw BadRequestException::create(
                detail: sprintf(Message::INVALID_VALUE_USE_ONE_OF, 'order'),
                additional: ['order' => $orders],
            );
        }

        $dirs = [
            'asc',
            'desc',
        ];

        $params['dir'] = $params['dir'] ?? 'desc';
        if (! in_array($params['dir'], $dirs)) {
            throw BadRequestException::create(
                detail: sprintf(Message::INVALID_VALUE_USE_ONE_OF, 'dir'),
                additional: ['dir' => $dirs],
            );
        }

        return $this->adminRepository->getAdmins($params);
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function updateAdmin(Admin $admin, array $data = []): Admin
    {
        if (isset($data['identity']) && $this->existsOther($data['identity'], $admin->getUuid()->toString())) {
            throw ConflictException::create(Message::DUPLICATE_IDENTITY);
        }

        if (! empty($data['password'])) {
            $admin->usePassword($data['password']);
        }

        if (isset($data['firstName'])) {
            $admin->setFirstName($data['firstName']);
        }

        if (isset($data['lastName'])) {
            $admin->setLastName($data['lastName']);
        }

        if (isset($data['status'])) {
            $admin->setStatus($data['status']);
        }

        if (! empty($data['roles'])) {
            $admin->resetRoles();
            foreach ($data['roles'] as $roleData) {
                $admin->addRole(
                    $this->adminRoleService->findOneBy(['uuid' => $roleData['uuid']])
                );
            }
        }

        if (! $admin->hasRoles()) {
            throw BadRequestException::create(Message::RESTRICTION_ROLES);
        }

        return $this->adminRepository->saveAdmin($admin);
    }
}
