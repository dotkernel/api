<?php

declare(strict_types=1);

namespace Api\App\Entity;

use DateTimeImmutable;
use Laminas\Stdlib\ArraySerializableInterface;

use function is_array;
use function method_exists;
use function ucfirst;

/**
 * Class AbstractEntity
 * @package Api\App\Entity
 */
abstract class AbstractEntity implements UuidAwareInterface, TimestampAwareInterface, ArraySerializableInterface
{
    use UuidAwareTrait;
    use TimestampAwareTrait;

    /**
     * AbstractEntity constructor.
     */
    public function __construct()
    {
        $this->uuid = UuidOrderedTimeGenerator::generateUuid();
        $this->created = new DateTimeImmutable();
        $this->updated = new DateTimeImmutable();
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
