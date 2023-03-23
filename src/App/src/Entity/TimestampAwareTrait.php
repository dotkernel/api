<?php

declare(strict_types=1);

namespace Api\App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

trait TimestampAwareTrait
{
    private string $dateFormat = 'Y-m-d H:i:s';

    /**
     * @ORM\Column(name="created", type="datetime_immutable")
     */
    protected ?DateTimeImmutable $created;

    /**
     * @ORM\Column(name="updated", type="datetime_immutable", nullable=true)
     */
    protected ?DateTimeImmutable $updated;

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * @return void
     */
    public function updateTimestamps(): void
    {
        $this->touch();
    }

    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    public function getCreatedFormatted(): ?string
    {
        if ($this->created instanceof DateTimeImmutable) {
            return $this->created->format($this->dateFormat);
        }

        return null;
    }

    public function getUpdated(): ?DateTimeImmutable
    {
        return $this->updated;
    }

    public function getUpdatedFormatted(): ?string
    {
        if ($this->updated instanceof DateTimeImmutable) {
            return $this->updated->format($this->dateFormat);
        }

        return null;
    }

    public function touch(): void
    {
        if (!($this->created instanceof DateTimeImmutable)) {
            $this->created = new DateTimeImmutable();
        }

        $this->updated = new DateTimeImmutable();
    }
}
