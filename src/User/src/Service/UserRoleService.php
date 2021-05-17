<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\User\Collection\UserRoleCollection;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserRoleRepository;
use Doctrine\ORM\EntityManager;
use Dot\AnnotatedServices\Annotation\Inject;

/**
 * Class UserRoleService
 * @package Api\User\Service
 */
class UserRoleService
{
    protected UserRoleRepository $roleRepository;

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
