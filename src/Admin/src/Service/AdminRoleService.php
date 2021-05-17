<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Repository\AdminRoleRepository;
use Dot\AnnotatedServices\Annotation\Inject;
use Doctrine\ORM\EntityManager;

/**
 * Class AdminRoleService
 * @package Api\Admin\Service
 */
class AdminRoleService
{
    protected AdminRoleRepository $adminRoleRepository;

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

        return $this->adminRoleRepository->findOneBy($params);
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
