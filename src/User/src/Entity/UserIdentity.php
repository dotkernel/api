<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Mezzio\Authentication\UserInterface;

/**
 * Class UserIdentity
 * @package Api\User\Entity
 */
class UserIdentity implements UserInterface
{
    /** @var string $identity */
    protected $identity;

    /** @var array $roles */
    protected $roles;

    /** @var array $details */
    protected $details;

    /**
     * UserIdentity constructor.
     * @param string $identity
     * @param array $roles
     * @param array $details
     */
    public function __construct(string $identity, array $roles = [], array $details = [])
    {
        $this->identity = $identity;
        $this->roles = $roles;
        $this->details = $details;
    }

    /**
     * Get the unique user identity (id, username, email address or ...)
     */
    public function getIdentity(): string
    {
        return $this->identity;
    }

    /**
     * @return iterable
     */
    public function getRoles(): iterable
    {
        return $this->roles;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getDetail(string $name, $default = null)
    {
        return $this->details[$name] ?? $default;
    }

    /**
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles = [])
    {
        $this->roles = $roles;
    }
}
