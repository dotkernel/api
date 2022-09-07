<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Api\User\Entity\UserRole;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Guest
 * @package Api\App\Entity
 */
class Guest
{
    /** @var string $identity */
    protected string $identity = 'guest';

    /** @var ArrayCollection<int, UserRole> */
    protected ArrayCollection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();

        $role = new UserRole();
        $role->setName(UserRole::ROLE_GUEST);
        $this->roles->add($role);
    }

    /**
     * @return string
     */
    public function getIdentity(): string
    {
        return $this->identity;
    }

    /**
     * @param string $identity
     * @return $this
     */
    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRoles(): ArrayCollection
    {
        return $this->roles;
    }

    /**
     * @param ArrayCollection $roles
     * @return $this
     */
    public function setRoles(ArrayCollection $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
