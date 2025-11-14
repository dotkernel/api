<?php

declare(strict_types=1);

namespace Core\Admin\Entity;

use Core\Admin\Enum\AdminStatusEnum;
use Mezzio\Authentication\UserInterface;

class AdminIdentity implements UserInterface
{
    /**
     * @param non-empty-string[] $roles
     * @param array<non-empty-string, string> $details
     */
    public function __construct(
        public string $id,
        public string $identity,
        public AdminStatusEnum $status,
        public array $roles = [],
        public array $details = [],
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function getStatus(): AdminStatusEnum
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->getStatus() === AdminStatusEnum::Active;
    }

    public function getRoles(): iterable
    {
        return $this->roles;
    }

    /**
     * @psalm-return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param mixed|null $default
     */
    public function getDetail(string $name, $default = null): mixed
    {
        return $this->details[$name] ?? $default;
    }
}
