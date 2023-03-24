<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Ramsey\Uuid\UuidInterface;

interface UuidAwareInterface
{
    public function getUuid(): ?UuidInterface;
}
