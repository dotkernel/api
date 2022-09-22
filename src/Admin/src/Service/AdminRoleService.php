<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Repository\AdminRoleRepository;
use Api\App\Entity\RoleInterface;
use Dot\AnnotatedServices\Annotation\Inject;

/**
 * Class AdminRoleService
 * @package Api\Admin\Service
 */
class AdminRoleService
{
    protected AdminRoleRepository $adminRoleRepository;

    /**
     * AdminRoleService constructor.
     * @param AdminRoleRepository $adminRoleRepository
     *
     * @Inject({AdminRoleRepository::class})
     */
    public function __construct(AdminRoleRepository $adminRoleRepository)
    {
        $this->adminRoleRepository = $adminRoleRepository;
    }

    /**
     * @param array $params
     * @return AdminRole|null
     */
    public function findOneBy(array $params = []): ?AdminRole
    {
        if (empty($params)) {
            return null;
        }

        return $this->adminRoleRepository->findOneBy($params);
    }

    /**
     * @return RoleInterface|null
     */
    public function getAdminRole(): ?RoleInterface
    {
        return $this->adminRoleRepository->findOneBy(
            ['name' => AdminRole::ROLE_ADMIN]
        );
    }

    /**
     * @return RoleInterface|null
     */
    public function getSuperUserRole(): ?RoleInterface
    {
        return $this->adminRoleRepository->findOneBy(
            ['name' => AdminRole::ROLE_SUPERUSER]
        );
    }

    /**
     * @param array $params
     * @return AdminRoleCollection
     */
    public function getRoles(array $params = []): AdminRoleCollection
    {
        return $this->adminRoleRepository->getRoles($params);
    }
}
