<?php

declare(strict_types=1);

namespace Api\App;

use Mezzio\Authentication\UserInterface;

class UserIdentity implements UserInterface
{
    protected string $identity;
    /** @var array<int|string, string> $roles */
    protected array $roles;
    protected array $details;

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

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
