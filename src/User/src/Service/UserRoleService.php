<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\User\Entity\UserRoleEntity;
use Api\User\Repository\UserRoleRepository;

/**
 * Class UserRoleService
 * @package Api\User\Service
 */
class UserRoleService
{
    /** @var UserRoleRepository $roleRepository */
    protected $roleRepository;

    /**
     * RoleService constructor.
     * @param UserRoleRepository $roleRepository
     */
    public function __construct(UserRoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param array $params
     * @return UserRoleEntity|null
     */
    public function findOneBy(array $params = []): ?UserRoleEntity
    {
        if (empty($params)) {
            return null;
        }

        /** @var UserRoleEntity $role */
        $role = $this->roleRepository->findOneBy($params);

        return $role;
    }
}
