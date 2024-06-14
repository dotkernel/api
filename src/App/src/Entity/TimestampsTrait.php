<?php

declare(strict_types=1);

namespace Api\App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
trait TimestampsTrait
{
    #[ORM\Column(name: "created", type: "datetime_immutable")]
    protected DateTimeImmutable $created;

    #[ORM\Column(name: "updated", type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $updated = null;

    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    public function getCreatedFormatted(string $dateFormat = 'Y-m-d H:i:s'): string
    {
        return $this->created->format($dateFormat);
    }

    public function getUpdated(): ?DateTimeImmutable
    {
        return $this->updated;
    }

    public function getUpdatedFormatted(string $dateFormat = 'Y-m-d H:i:s'): ?string
    {
        if ($this->updated instanceof DateTimeImmutable) {
            return $this->updated->format($dateFormat);
        }

        return null;
    }

    #[ORM\PrePersist]
    public function created(): void
    {
        $this->created = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updated = new DateTimeImmutable();
    }
}
