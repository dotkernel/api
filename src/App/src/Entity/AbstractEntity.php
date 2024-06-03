<?php

declare(strict_types=1);

namespace Api\App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Stdlib\ArraySerializableInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use function is_array;
use function method_exists;
use function ucfirst;

abstract class AbstractEntity implements ArraySerializableInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'uuid', type: "uuid_binary", unique: true)]
    protected UuidInterface $uuid;

    #[ORM\Column(name: "created", type: "datetime_immutable")]
    protected DateTimeImmutable $created;

    #[ORM\Column(name: "updated", type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $updated = null;

    public function __construct()
    {
        $this->uuid    = Uuid::uuid4();
        $this->created = new DateTimeImmutable();
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getCreated(): ?DateTimeImmutable
    {
        return $this->created;
    }

    public function getCreatedFormatted(string $dateFormat = 'Y-m-d H:i:s'): string
    {
        return $this->created->format($dateFormat);
    }

    public function getUpdated(): ?DateTimeImmutable
    {
        return $this->updated;
    }

    public function getUpdatedFormatted(string $dateFormat = 'Y-m-d H:i:s'): ?string
    {
        if ($this->updated instanceof DateTimeImmutable) {
            return $this->updated->format($dateFormat);
        }

        return null;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updated = new DateTimeImmutable();
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
