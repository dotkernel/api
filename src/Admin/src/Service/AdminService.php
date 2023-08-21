<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Repository\AdminRepository;
use Api\App\Message;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;

use function sprintf;

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
     * @throws Exception
     */
    public function createAdmin(array $data = []): Admin
    {
        if ($this->exists($data['identity'])) {
            throw new Exception(Message::DUPLICATE_IDENTITY);
        }

        $admin = (new Admin())
            ->setIdentity($data['identity'])
            ->usePassword($data['password'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setStatus($data['status'] ?? Admin::STATUS_ACTIVE);

        if (! empty($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                $role = $this->adminRoleService->findOneBy(['uuid' => $roleData['uuid']]);
                if (! $role instanceof AdminRole) {
                    throw new Exception(
                        sprintf(Message::NOT_FOUND_BY_UUID, 'role', $roleData['uuid'])
                    );
                }
                $admin->addRole($role);
            }
        } else {
            $role = $this->adminRoleService->findOneBy(['name' => AdminRole::ROLE_ADMIN]);
            if (! $role instanceof AdminRole) {
                throw new Exception(
                    sprintf(Message::NOT_FOUND_BY_NAME, 'role', AdminRole::ROLE_ADMIN)
                );
            }
            $admin->addRole($role);
        }

        return $this->adminRepository->saveAdmin($admin);
    }

    /**
     * @throws Exception
     */
    public function deleteAdmin(Admin $admin): void
    {
        $this->adminRepository->deleteAdmin(
            $admin->resetRoles()->deactivate()
        );
    }

    public function exists(string $identity = ''): bool
    {
        return $this->findOneBy(['identity' => $identity]) instanceof Admin;
    }

    public function findOneBy(array $params = []): ?Admin
    {
        return $this->adminRepository->findOneBy($params);
    }

    public function getAdmins(array $params = []): AdminCollection
    {
        return $this->adminRepository->getAdmins($params);
    }

    /**
     * @throws Exception
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
                $role = $this->adminRoleService->findOneBy(['uuid' => $roleData['uuid']]);
                if (! $role instanceof AdminRole) {
                    throw new Exception(
                        sprintf(Message::NOT_FOUND_BY_UUID, 'role', $roleData['uuid'])
                    );
                }
                $admin->addRole($role);
            }
        }

        return $this->adminRepository->saveAdmin($admin);
    }
}
