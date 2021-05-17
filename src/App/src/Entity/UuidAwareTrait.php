<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Throwable;

/**
 * Trait UuidAwareTrait
 * @package Api\App\Entity
 */
trait UuidAwareTrait
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="uuid", type="uuid_binary_ordered_time", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")
    */
    protected ?UuidInterface $uuid;

    /**
     * @return UuidInterface|null
     */
    public function getUuid(): ?UuidInterface
    {
        if (!$this->uuid) {
            try {
                $this->uuid = UuidOrderedTimeGenerator::generateUuid();
            } catch (Throwable $exception) {
                #TODO save the error message
            }
        }

        return $this->uuid;
    }
}
