<?php

declare(strict_types=1);

namespace Api\App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Stdlib\ArraySerializableInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use function is_array;
use function method_exists;
use function ucfirst;

#[ORM\MappedSuperclass]
abstract class AbstractEntity implements ArraySerializableInterface, EntityInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'uuid', type: "uuid_binary", unique: true)]
    protected UuidInterface $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function exchangeArray(array $array): void
    {
        foreach ($array as $property => $values) {
            if (is_array($values)) {
                $method = 'add' . ucfirst($property);
                if (! method_exists($this, $method)) {
                    continue;
                }
                foreach ($values as $value) {
                    $this->$method($value);
                }
            } else {
                $method = 'set' . ucfirst($property);
                if (! method_exists($this, $method)) {
                    continue;
                }
                $this->$method($values);
            }
        }
    }
}
