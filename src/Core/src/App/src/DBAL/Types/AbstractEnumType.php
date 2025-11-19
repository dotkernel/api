<?php

declare(strict_types=1);

namespace Core\App\DBAL\Types;

use BackedEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Type;

use function array_map;
use function implode;
use function sprintf;

abstract class AbstractEnumType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($platform instanceof PostgreSQLPlatform) {
            return static::NAME;
        }

        if ($platform instanceof SQLitePlatform) {
            return 'TEXT';
        }

        $values = array_map(fn($case) => "'$case->value'", $this->getEnumCases());

        return sprintf('ENUM(%s)', implode(', ', $values));
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $this->getValue($value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $this->getValue($value);
    }

    /**
     * @return class-string
     */
    abstract public function getEnumClass(): string;

    /**
     * @return non-empty-string
     */
    abstract public function getName(): string;

    /**
     * @return BackedEnum[]
     */
    public function getEnumCases(): array
    {
        return $this->getEnumClass()::cases();
    }

    /**
     * @return list<BackedEnum>
     */
    public function getEnumValues(): array
    {
        return $this->getEnumClass()::values();
    }

    public function getValue(mixed $value): mixed
    {
        if (! $value instanceof BackedEnum) {
            return $value;
        }

        return $value->value;
    }
}
