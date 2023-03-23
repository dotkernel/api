<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Api\User\Entity\UserRole;
use Doctrine\Common\Collections\ArrayCollection;

class Guest
{
    protected string $identity = 'guest';

    /** @var ArrayCollection<int, UserRole> */
    protected ArrayCollection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();

        $this->roles->add(
            (new UserRole())->setName(UserRole::ROLE_GUEST)
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

    public function getRoles(): ArrayCollection
    {
        return $this->roles;
    }

    public function setRoles(ArrayCollection $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
