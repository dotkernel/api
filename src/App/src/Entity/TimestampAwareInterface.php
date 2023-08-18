<?php

declare(strict_types=1);

namespace Api\App\Entity;

use DateTimeImmutable;

interface TimestampAwareInterface
{
    public function getCreated(): ?DateTimeImmutable;

    public function getUpdated(): ?DateTimeImmutable;

    public function touch(): void;
}
