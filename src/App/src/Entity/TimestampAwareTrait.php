<?php

declare(strict_types=1);

namespace Api\App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait TimestampAwareTrait
 * @package Api\App\Entity
 */
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

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @return string|null
     */
    public function getCreatedFormatted(): ?string
    {
        if ($this->created instanceof DateTimeImmutable) {
            return $this->created->format($this->dateFormat);
        }

        return null;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdated(): ?DateTimeImmutable
    {
        return $this->updated;
    }

    /**
     * @return string|null
     */
    public function getUpdatedFormatted(): ?string
    {
        if ($this->updated instanceof DateTimeImmutable) {
            return $this->updated->format($this->dateFormat);
        }

        return null;
    }

    /**
     * Update internal timestamps
     * @return $this
     */
    public function touch(): self
    {
        if (!($this->created instanceof DateTimeImmutable)) {
            $this->created = new DateTimeImmutable();
        }

        $this->updated = new DateTimeImmutable();

        return $this;
    }
}
