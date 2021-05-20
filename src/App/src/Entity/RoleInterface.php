<?php

declare(strict_types=1);

namespace Api\App\Entity;

/**
 * Interface RoleInterface
 * @package Api\App\Entity
 */
interface RoleInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return RoleInterface
     */
    public function setName(string $name): RoleInterface;
}
