<?php

declare(strict_types=1);

namespace Api\App\Common;

use DateTime;

/**
 * Interface TimestampAwareInterface
 * @package Api\App\Common
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
