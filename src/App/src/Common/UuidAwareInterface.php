<?php

declare(strict_types=1);

namespace Api\App\Common;

use Ramsey\Uuid\UuidInterface;

/**
 * Interface UuidAwareInterface
 * @package Api\App\Common
 */
interface UuidAwareInterface
{
    public function getUuid(): UuidInterface;
}
