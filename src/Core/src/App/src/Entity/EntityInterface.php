<?php

declare(strict_types=1);

namespace Core\App\Entity;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

interface EntityInterface
{
    public function getUuid(): UuidInterface;

    public function getCreated(): ?DateTimeImmutable;

    public function getCreatedFormatted(string $dateFormat = 'Y-m-d H:i:s'): string;

    public function getUpdated(): ?DateTimeImmutable;

    public function getUpdatedFormatted(string $dateFormat = 'Y-m-d H:i:s'): ?string;

    public function isDeleted(): bool;
}
