<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\User\Collection\UserRoleCollection;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserRoleRepository;
use Dot\AnnotatedServices\Annotation\Inject;

/**
 * Class UserRoleService
 * @package Api\User\Service
 */
class UserRoleService
{
    protected UserRoleRepository $roleRepository;

    /**
     * UserRoleService constructor.
     * @param UserRoleRepository $roleRepository
     *
     * @Inject({UserRoleRepository::class})
     */
    public function __construct(UserRoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param array $params
     * @return UserRole|null
     */
    public function findOneBy(array $params = []): ?UserRole
    {
        if (empty($params)) {
            return null;
        }

        return $this->roleRepository->findOneBy($params);
    }

    /**
     * @param array $params
     * @return UserRoleCollection
     */
    public function getRoles(array $params = []): UserRoleCollection
    {
        return $this->roleRepository->getRoles($params);
    }
}
