<?php

declare(strict_types=1);

namespace Core\App\DBAL\Types;

use BackedEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Types\Type;

use function array_map;
use function implode;
use function sprintf;

abstract class AbstractEnumType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($platform instanceof SQLitePlatform) {
            return 'TEXT';
        }

        $values = array_map(fn($case) => "'$case->value'", $this->getEnumValues());

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
    abstract protected function getEnumClass(): string;

    /**
     * @return BackedEnum[]
     */
    private function getEnumValues(): array
    {
        return $this->getEnumClass()::cases();
    }

    private function getValue(mixed $value): mixed
    {
        if (! $value instanceof BackedEnum) {
            return $value;
        }

        return $value->value;
    }
}
