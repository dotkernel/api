<?php

declare(strict_types=1);

namespace App\Common;

use Ramsey\Uuid\UuidInterface;

/**
 * Interface UuidAwareInterface
 * @package Frontend\Core\Common
 */
interface UuidAwareInterface
{
    public function getUuid(): UuidInterface;
}
