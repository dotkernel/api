<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\User\Entity\AdminRole;
use Api\User\Repository\AdminRoleRepository;
use Doctrine\ORM\EntityManager;

/**
 * Class AdminRoleService
 * @package Api\User\Service
 */
class AdminRoleService
{
    /** @var AdminRoleRepository */
    protected $adminRoleRepository;

    /**
     * RoleService constructor.
     * @param EntityManager $entityManager
     *
     * @Inject({EntityManager::class})
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->adminRoleRepository = $entityManager->getRepository(AdminRole::class);
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

        /** @var AdminRole $role */
        $role = $this->adminRoleRepository->findOneBy($params);

        return $role;
    }
}
