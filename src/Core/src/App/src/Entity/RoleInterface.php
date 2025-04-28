<?php

declare(strict_types=1);

namespace Core\App\Entity;

use BackedEnum;

interface RoleInterface extends EntityInterface
{
    public function getName(): ?BackedEnum;

    public function setName(BackedEnum $name): RoleInterface;
}
