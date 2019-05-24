<?php

declare(strict_types=1);

namespace App\Common;

use DateTime;

/**
 * Class AbstractEntity
 * @package App\Common
 */
abstract class AbstractEntity implements UuidAwareInterface, TimestampAwareInterface
{
    use UuidAwareTrait;
    use TimestampAwareTrait;

    /**
     * AbstractEntity constructor.
     */
    public function __construct()
    {
        $this->uuid = UuidOrderedTimeGenerator::generateUuid();
        $this->created = new DateTime('now');
        $this->updated = new DateTime('now');
    }
}
