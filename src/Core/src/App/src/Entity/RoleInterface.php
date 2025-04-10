<?php

declare(strict_types=1);

namespace Core\App\Entity;

use BackedEnum;
use Ramsey\Uuid\UuidInterface;

interface RoleInterface
{
    public function getUuid(): UuidInterface;

    public function getName(): ?BackedEnum;

    public function setName(BackedEnum $name): RoleInterface;
}
