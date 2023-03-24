<?php

declare(strict_types=1);

namespace Api\App\Entity;

interface RoleInterface
{
    public function getName(): string;

    public function setName(string $name): RoleInterface;
}
