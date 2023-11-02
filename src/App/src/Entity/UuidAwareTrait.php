<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

trait UuidAwareTrait
{
    #[ORM\Id]
    #[ORM\Column(name: 'uuid', type: "uuid_binary_ordered_time", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(\Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator::class)]
    protected ?UuidInterface $uuid;

    public function getUuid(): ?UuidInterface
    {
        if (! $this->uuid) {
            $this->uuid = UuidOrderedTimeGenerator::generateUuid();
        }

        return $this->uuid;
    }
}
