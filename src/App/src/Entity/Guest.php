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
    protected string $identity = 'guest';

    protected $roles;

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
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
