<?php

declare(strict_types=1);

namespace Api\User\Service;

use Api\App\Exception\NotFoundException;
use Api\App\Message;
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
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function findOneBy(array $params = []): UserRole
    {
        $role = $this->roleRepository->findOneBy($params);
        if (! $role instanceof UserRole) {
            throw new NotFoundException(Message::ROLE_NOT_FOUND);
        }

        return $role;
    }

    public function getRoles(array $params = []): UserRoleCollection
    {
        return $this->roleRepository->getRoles($params);
    }
}
