<?php

declare(strict_types=1);

namespace Core\App\Entity;

use BackedEnum;
use DateTimeImmutable;

/**
 * @phpstan-type RoleType array{
 *     id: non-empty-string,
 *     name: non-empty-string,
 *     created: DateTimeImmutable|null,
 *     updated: DateTimeImmutable|null
 * }
 */
interface RoleInterface extends EntityInterface
{
    public function getName(): BackedEnum;

    public function setName(BackedEnum $name): RoleInterface;

    /**
     * @return RoleType
     */
    public function getArrayCopy(): array;
}
