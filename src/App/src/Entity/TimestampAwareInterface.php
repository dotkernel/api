<?php

declare(strict_types=1);

namespace Api\App\Entity;

use DateTimeImmutable;

/**
 * Interface TimestampAwareInterface
 * @package Api\App\Entity
 */
interface TimestampAwareInterface
{
    /**
     * @return DateTimeImmutable|null
     */
    public function getCreated(): ?DateTimeImmutable;

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdated(): ?DateTimeImmutable;

    /**
     * Update internal timestamps
     * @return self
     */
    public function touch(): self;
}
