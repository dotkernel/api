<?php

declare(strict_types=1);

namespace Api\User\Service;

use Dot\AnnotatedServices\Annotation\Inject;
use Api\App\Common\Message;
use Api\User\Entity\AdminRole;
use Api\User\Entity\Admin;
use Api\User\Repository\AdminRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\EntityManager;

/**
 * Class AdminService
 * @package Api\User\Service
 */
class AdminService
{
    /** @var AdminRepository $adminRepository */
    protected $adminRepository;

    /** @var AdminRoleService $adminRoleService */
    protected AdminRoleService $adminRoleService;

    /**
     * AdminService constructor.
     * @param EntityManager $entityManager
     * @param AdminRoleService $adminRoleService
     *
     * @Inject({EntityManager::class, AdminRoleService::class})
     */
    public function __construct(EntityManager $entityManager, AdminRoleService $adminRoleService)
    {
        $this->adminRepository = $entityManager->getRepository(Admin::class);
        $this->adminRoleService = $adminRoleService;
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

        /** @var Admin $admin */
        $admin = $this->adminRepository->findOneBy($params);

        return $admin;
    }

    /**
     * @param string $identity
     * @param string|null $uuid
     * @return bool
     */
    public function exists(string $identity = '', ?string $uuid = '')
    {
        return !is_null(
            $this->adminRepository->exists($identity, $uuid)
        );
    }

    /**
     * @param Admin $admin
     * @param array $data
     * @return Admin
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateAdmin(Admin $admin, array $data = [])
    {
        if (isset($data['identity']) && !is_null($data['identity'])) {
            if ($this->exists($data['identity'], $admin->getUuid()->toString())) {
                throw new ORMException(Message::DUPLICATE_IDENTITY);
            }
            $admin->setIdentity($data['identity']);
        }

        if (isset($data['password']) && !is_null($data['password'])) {
            $admin->setPassword(
                password_hash($data['password'], PASSWORD_DEFAULT)
            );
        }

        if (isset($data['status']) && !empty($data['status'])) {
            $admin->setStatus($data['status']);
        }

        if (isset($data['firstname']) && !is_null($data['firstname'])) {
            $admin->setFirstname($data['firstname']);
        }

        if (isset($data['lastname']) && !is_null($data['lastname'])) {
            $admin->setLastname($data['lastname']);
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
            throw new \Exception(Message::RESTRICTION_ROLES);
        }

        $this->adminRepository->saveAdmin($admin);

        return $admin;
    }

    /**
     * @param array $data
     * @return Admin
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createAdmin(array $data = [])
    {
        if ($this->exists($data['identity'])) {
            throw new ORMException(Message::DUPLICATE_IDENTITY);
        }

        $admin = new Admin();
        $admin->setIdentity($data['identity']);
        $admin->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));

        if (!empty($data['firstname'])) {
            $admin->setFirstName($data['firstname']);
        }
        if (!empty($data['lastname'])) {
            $admin->setLastname($data['lastname']);
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
            throw new \Exception(Message::RESTRICTION_ROLES);
        }

        $this->adminRepository->saveAdmin($admin);

        return $admin;
    }

    /**
     * @return AdminRepository
     */
    public function getAdminRepository(): AdminRepository
    {
        return $this->adminRepository;
    }

    /**
     * @return AdminRoleService
     */
    public function getAdminRoleService(): AdminRoleService
    {
        return $this->adminRoleService;
    }
}
