<?php

declare(strict_types=1);

namespace Api\App;

use Mezzio\Authentication\UserInterface;

class UserIdentity implements UserInterface
{
    /** @var string $identity */
    protected string $identity;

    /** @var iterable<int|string, string> $roles */
    protected array $roles;

    /** @var array $details */
    protected array $details;

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
     * @psalm-return iterable<int|string, string>
     * @return iterable
     */
    public function getRoles(): iterable
    {
        return $this->roles;
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getDetail(string $name, $default = null): mixed
    {
        return $this->details[$name] ?? $default;
    }

    /**
     * @psalm-return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
