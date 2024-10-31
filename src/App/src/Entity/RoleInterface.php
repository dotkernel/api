<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Ramsey\Uuid\UuidInterface;

interface RoleInterface
{
    public function getUuid(): UuidInterface;

    public function getName(): ?string;

    public function setName(string $name): RoleInterface;
}
