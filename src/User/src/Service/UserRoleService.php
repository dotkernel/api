<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\User\Entity\UserRoleEntity;
use Api\User\Repository\UserRoleRepository;
use Doctrine\ORM\EntityManager;
use Dot\AnnotatedServices\Annotation\Inject;

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
        $this->roleRepository = $entityManager->getRepository(UserRoleEntity::class);
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
