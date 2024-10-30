<?php

declare(strict_types=1);

namespace Api\App\DBAL\Types;

use BackedEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

use function array_map;
use function gettype;
use function implode;
use function is_object;
use function sprintf;

abstract class AbstractEnumType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($platform instanceof SQLitePlatform) {
            return 'TEXT';
        }

        $values = array_map(fn($case) => "'{$case->value}'", $this->getEnumValues());

        return sprintf('ENUM(%s)', implode(', ', $values));
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->getEnumClass()::from($value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        if (! $value instanceof BackedEnum) {
            throw new InvalidArgumentException(sprintf(
                'Expected instance of %s, got %s',
                $this->getEnumClass(),
                is_object($value) ? $value::class : gettype($value)
            ));
        }

        return $value->value;
    }

    /**
     * @return class-string
     */
    abstract protected function getEnumClass(): string;

    private function getEnumValues(): array
    {
        return $this->getEnumClass()::cases();
    }
}
