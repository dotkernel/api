<?php

declare(strict_types=1);

namespace Api\User\Service;

use Dot\AnnotatedServices\Annotation\Inject;
use Api\User\Entity\Admin;
use Api\User\Repository\AdminRepository;
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
    protected $adminRoleService;

    /**
     * AdminService constructor.
     * @param EntityManager $entityManager
     *
     * @Inject({EntityManager::class})
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->adminRepository = $entityManager->getRepository(Admin::class);
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
}
