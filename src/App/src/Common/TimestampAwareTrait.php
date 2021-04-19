<?php

declare(strict_types=1);

namespace Api\App\Common;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * Trait TimestampAwareTrait
 * @package Api\App\Common
 */
trait TimestampAwareTrait
{
    /**
     * @var string $dateFormat
     */
    private $dateFormat = 'Y-m-d H:i:s';

    /**
     * @ORM\Column(name="created", type="datetime_immutable")
     * @var DateTimeImmutable $created
     */
    protected $created;

    /**
     * @ORM\Column(name="updated", type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable $updated
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
     * @return DateTimeImmutable
     */
    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @return null|string
     */
    public function getCreatedFormatted()
    {
        if ($this->created instanceof DateTimeImmutable) {
            return $this->created->format($this->dateFormat);
        }

        return null;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getUpdated(): ?DateTimeImmutable
    {
        return $this->updated;
    }

    /**
     * @return null|string
     */
    public function getUpdatedFormatted()
    {
        if ($this->updated instanceof DateTimeImmutable) {
            return $this->updated->format($this->dateFormat);
        }

        return null;
    }

    /**
     * @return $this
     */
    public function touch()
    {
        try {
            if (!($this->created instanceof DateTimeImmutable)) {
                $this->created = new DateTimeImmutable('now');
            }

            $this->updated = new DateTimeImmutable('now');
        } catch (Exception $exception) {
            #TODO save the error message
        }

        return $this;
    }
}
