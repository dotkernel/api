<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Repository\AdminRepository;
use Api\App\Message;
use Doctrine\ORM\ORMException;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;

/**
 * Class AdminService
 * @package Api\Admin\Service
 */
class AdminService
{
    protected AdminRoleService $adminRoleService;

    protected AdminRepository $adminRepository;

    /**
     * AdminService constructor.
     * @param AdminRoleService $adminRoleService
     * @param AdminRepository $adminRepository
     *
     * @Inject({AdminRoleService::class, AdminRepository::class})
     */
    public function __construct(AdminRoleService $adminRoleService, AdminRepository $adminRepository)
    {
        $this->adminRoleService = $adminRoleService;
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param Admin $admin
     * @return void
     * @throws Exception
     */
    public function deleteAdmin(Admin $admin): void
    {
        $this->adminRepository->deleteAdmin($admin);
    }

    /**
     * @param string $identity
     * @param string|null $uuid
     * @return bool
     */
    public function exists(string $identity = '', ?string $uuid = ''): bool
    {
        return !is_null(
            $this->adminRepository->exists($identity, $uuid)
        );
    }

    /**
     * @param string $identity
     * @return Admin|null
     */
    public function findByIdentity(string $identity): ?Admin
    {
        return $this->adminRepository->findOneBy([
            'identity' => $identity
        ]);
    }

    /**
     * @param array $params
     * @return Admin|null
     */
    public function findOneBy(array $params = []): ?Admin
    {
        if (empty($params)) {
            return null;
        }

        return $this->adminRepository->findOneBy($params);
    }

    /**
     * @param array $params
     * @return AdminCollection
     */
    public function getAdmins(array $params = []): AdminCollection
    {
        return $this->adminRepository->getAdmins($params);
    }

    /**
     * @param Admin $admin
     * @param array $data
     * @return Admin
     * @throws Exception
     */
    public function updateAdmin(Admin $admin, array $data = []): Admin
    {
        if (isset($data['password']) && !is_null($data['password'])) {
            $admin->setPassword(
                password_hash($data['password'], PASSWORD_DEFAULT)
            );
        }

        if (isset($data['status']) && !empty($data['status'])) {
            $admin->setStatus($data['status']);
        }

        if (isset($data['firstName']) && !is_null($data['firstName'])) {
            $admin->setFirstName($data['firstName']);
        }

        if (isset($data['lastName']) && !is_null($data['lastName'])) {
            $admin->setLastName($data['lastName']);
        }

        if (!empty($data['roles'])) {
            $admin->resetRoles();
            foreach ($data['roles'] as $roleData) {
                $role = $this->adminRoleService->findOneBy(['uuid' => $roleData['uuid']]);
                if ($role instanceof AdminRole) {
                    $admin->addRole($role);
                }
            }
        }
        if ($admin->getRoles()->count() === 0) {
            throw new Exception(Message::RESTRICTION_ROLES);
        }

        $this->adminRepository->saveAdmin($admin);

        return $admin;
    }

    /**
     * @param array $data
     * @return Admin
     * @throws Exception
     */
    public function createAdmin(array $data = []): Admin
    {
        if ($this->exists($data['identity'])) {
            throw new ORMException(Message::DUPLICATE_IDENTITY);
        }

        $admin = new Admin();
        $admin->setIdentity($data['identity']);
        $admin->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));

        if (!empty($data['firstName'])) {
            $admin->setFirstName($data['firstName']);
        }
        if (!empty($data['lastName'])) {
            $admin->setLastName($data['lastName']);
        }

        if (!empty($data['status'])) {
            $admin->setStatus($data['status']);
        }

        if (!empty($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                $role = $this->adminRoleService->findOneBy(['uuid' => $roleData['uuid']]);
                if ($role instanceof AdminRole) {
                    $admin->addRole($role);
                }
            }
        } else {
            $role = $this->adminRoleService->findOneBy(['name' => AdminRole::ROLE_ADMIN]);
            if ($role instanceof AdminRole) {
                $admin->addRole($role);
            }
        }
        if ($admin->getRoles()->count() === 0) {
            throw new Exception(Message::RESTRICTION_ROLES);
        }

        $this->adminRepository->saveAdmin($admin);

        return $admin;
    }
}
