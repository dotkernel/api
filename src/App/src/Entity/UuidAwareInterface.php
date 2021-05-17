<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Ramsey\Uuid\UuidInterface;

/**
 * Interface UuidAwareInterface
 * @package Api\App\Entity
 */
interface UuidAwareInterface
{
    /**
     * @return UuidInterface|null
     */
    public function getUuid(): ?UuidInterface;
}
