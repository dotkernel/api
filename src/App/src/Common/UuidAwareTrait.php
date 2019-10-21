<?php

declare(strict_types=1);

namespace Api\App\Common;

use Exception;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * Trait UuidAwareTrait
 * @package Api\App\Common
 */
trait UuidAwareTrait
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="uuid", type="uuid_binary_ordered_time", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")
     * @var UuidInterface
    */
    protected $uuid;

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        if (!$this->uuid) {
            try {
                $this->uuid = UuidOrderedTimeGenerator::generateUuid();
            } catch (Exception $exception) {
                #TODO save the error message
            }
        }

        return $this->uuid;
    }
}
