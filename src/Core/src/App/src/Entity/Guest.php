<?php

declare(strict_types=1);

namespace Core\App\Entity;

use Core\User\Entity\UserRole;
use Core\User\Enum\UserRoleEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Guest
{
    protected string $identity = UserRoleEnum::Guest->value;

    /** @var Collection<int, RoleInterface> */
    protected Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();

        $this->roles->add(
            (new UserRole())->setName(UserRoleEnum::Guest)
        );
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles->toArray();
    }

    /**
     * @param RoleInterface[] $roles
     */
    public function setRoles(array $roles): self
    {
        foreach ($roles as $role) {
            $this->roles->add($role);
        }

        return $this;
    }
}
