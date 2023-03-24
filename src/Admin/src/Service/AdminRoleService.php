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
    ) {}

    public function findOneBy(array $params = []): ?AdminRole
    {
        return $this->adminRoleRepository->findOneBy($params);
    }

    public function getAdminRoles(array $params = []): AdminRoleCollection
    {
        return $this->adminRoleRepository->getAdminRoles($params);
    }
}
