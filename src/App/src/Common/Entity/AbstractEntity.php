<?php

declare(strict_types=1);

namespace Api\App\Common\Entity;

use Api\App\Common\TimestampAwareInterface;
use Api\App\Common\TimestampAwareTrait;
use Api\App\Common\UuidAwareInterface;
use Api\App\Common\UuidAwareTrait;
use Api\App\Common\UuidOrderedTimeGenerator;
use DateTimeImmutable;

/**
 * Class AbstractEntity
 * @package Api\App\Common
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
        $this->created = new DateTimeImmutable('now');
        $this->updated = new DateTimeImmutable('now');
    }

    /**
     * Exchange internal values from provided array
     *
     * @param array $data
     * @return void
     */
    public function exchangeArray(array $data)
    {
        foreach ($data as $property => $values) {
            if (is_array($values)) {
                $method = 'add' . ucfirst($property);
                if (!method_exists($this, $method)) {
                    continue;
                }
                foreach ($values as $value) {
                    $this->$method($value);
                }
            } else {
                $method = 'set' . ucfirst($property);
                if (!method_exists($this, $method)) {
                    continue;
                }
                $this->$method($values);
            }
        }
    }
}
