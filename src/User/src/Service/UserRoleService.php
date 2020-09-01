<?php

declare(strict_types=1);

namespace Api\User\Service;

use Dot\AnnotatedServices\Annotation\Inject;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserRoleRepository;
use Doctrine\ORM\EntityManager;

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
     * @param EntityManager $entityManager
     *
     * @Inject({EntityManager::class})
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->roleRepository = $entityManager->getRepository(UserRole::class);
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

        /** @var UserRole $role */
        $role = $this->roleRepository->findOneBy($params);

        return $role;
    }
}
