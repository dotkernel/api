<?php

declare(strict_types=1);

namespace App\Common;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait TimestampAwareTrait
 * @package App\Common
 */
trait TimestampAwareTrait
{
    /**
     * @var string $dateFormat
     */
    private $dateFormat = 'Y-m-d H:i:s';

    /**
     * @ORM\Column(name="created", type="datetime")
     * @var DateTime
     */
    protected $created;

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     * @var DateTime
     */
    protected $updated;

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateTimestamps()
    {
        $this->touch();
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @return null|string
     */
    public function getCreatedFormatted()
    {
        if ($this->created instanceof DateTime) {
            return $this->created->format($this->dateFormat);
        }

        return null;
    }

    /**
     * @return DateTime
     */
    public function getUpdated(): ?DateTime
    {
        return $this->updated;
    }

    /**
     * @return null|string
     */
    public function getUpdatedFormatted()
    {
        if ($this->updated instanceof DateTime) {
            return $this->updated->format($this->dateFormat);
        }

        return null;
    }

    /**
     * Update the timestamps
     */
    public function touch()
    {
        if (!$this->created) {
            $this->created = new DateTime('now');
        }

        $this->updated = new DateTime('now');
    }
}
