<?php

declare(strict_types=1);

namespace App\Common;

use DateTime;

/**
 * Interface TimestampAwareInterface
 * @package Frontend\Core\Entity
 */
interface TimestampAwareInterface
{
    /**
     * @return DateTime|null
     */
    public function getCreated(): DateTime;

    /**
     * @return DateTime|null
     */
    public function getUpdated(): ?DateTime;

    /**
     * Update internal timestamps
     */
    public function touch();
}
