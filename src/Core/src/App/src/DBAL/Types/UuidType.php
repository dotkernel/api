<?php

declare(strict_types=1);

namespace Core\App\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class UuidType extends \Ramsey\Uuid\Doctrine\UuidType
{
    public const NAME = 'uuid';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'UUID';
    }
}
