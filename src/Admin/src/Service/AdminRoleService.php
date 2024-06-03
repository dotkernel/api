<?php

declare(strict_types=1);

namespace Api\Admin\Service;

use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Repository\AdminRoleRepository;
use Dot\AnnotatedServices\Annotation\Inject;

class AdminRoleService implements AdminRoleServiceInterface
{
    /**
     * @Inject({
     *     AdminRoleRepository::class
     * })
     */
    public function __construct(
        protected AdminRoleRepository $adminRoleRepository
    ) {
    }

    public function findOneBy(array $params = []): ?AdminRole
    {
        $role = $this->adminRoleRepository->findOneBy($params);
        if ($role instanceof AdminRole) {
            return $role;
        }

        return null;
    }

    public function getAdminRoles(array $params = []): AdminRoleCollection
    {
        return $this->adminRoleRepository->getAdminRoles($params);
    }
}
