<?php

declare(strict_types=1);

namespace Core\User;

use Mezzio\Authentication\UserInterface;

class UserIdentity implements UserInterface
{
    /** @var non-empty-string $identity */
    protected string $identity;
    /** @var array<int, mixed> $roles */
    protected array $roles;
    /** @var array<non-empty-string, mixed> $details */
    protected array $details;

    /**
     * @param non-empty-string $identity
     * @param array<int, mixed> $roles
     * @param array<non-empty-string, mixed> $details
     */
    public function __construct(string $identity, array $roles = [], array $details = [])
    {
        $this->identity = $identity;
        $this->roles    = $roles;
        $this->details  = $details;
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function getRoles(): iterable
    {
        return $this->roles;
    }

    /**
     * @param null|mixed $default
     */
    public function getDetail(string $name, $default = null): mixed
    {
        return $this->details[$name] ?? $default;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param array<int, mixed> $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
