<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Repository\AdminRepository;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Message;
use Dot\AnnotatedServices\Annotation\Inject;

class AdminService implements AdminServiceInterface
{
    /**
     * @Inject({
     *     AdminRoleService::class,
     *     AdminRepository::class
     * })
     */
    public function __construct(
        protected AdminRoleService $adminRoleService,
        protected AdminRepository $adminRepository
    ) {
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function createAdmin(array $data = []): Admin
    {
        if ($this->exists($data['identity'])) {
            throw new ConflictException(Message::DUPLICATE_IDENTITY);
        }

        $admin = (new Admin())
            ->setIdentity($data['identity'])
            ->usePassword($data['password'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setStatus($data['status'] ?? Admin::STATUS_ACTIVE);

        if (! empty($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                $admin->addRole(
                    $this->adminRoleService->findOneBy(['uuid' => $roleData['uuid']])
                );
            }
        } else {
            $admin->addRole(
                $this->adminRoleService->findOneBy(['name' => AdminRole::ROLE_ADMIN])
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

    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params = []): Admin
    {
        $admin = $this->adminRepository->findOneBy($params);
        if (! $admin instanceof Admin) {
            throw new NotFoundException(Message::ADMIN_NOT_FOUND);
        }

        return $admin;
    }

    public function getAdmins(array $params = []): AdminCollection
    {
        return $this->adminRepository->getAdmins($params);
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function updateAdmin(Admin $admin, array $data = []): Admin
    {
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

        return $this->adminRepository->saveAdmin($admin);
    }
}
