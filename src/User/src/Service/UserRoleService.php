<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\User\Collection\UserRoleCollection;
use Api\User\Entity\UserRole;
use Api\User\Repository\UserRoleRepository;
use Dot\AnnotatedServices\Annotation\Inject;

class UserRoleService implements UserRoleServiceInterface
{
    /**
     * @Inject({
     *     UserRoleRepository::class
     * })
     */
    public function __construct(
        protected UserRoleRepository $roleRepository
    ) {}

    public function findOneBy(array $params = []): ?UserRole
    {
        return $this->roleRepository->findOneBy($params);
    }

    public function getRoles(array $params = []): UserRoleCollection
    {
        return $this->roleRepository->getRoles($params);
    }
}
