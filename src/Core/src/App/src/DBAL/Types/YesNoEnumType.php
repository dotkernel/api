<?php

declare(strict_types=1);

namespace Core\App\DBAL\Types;

use Core\App\Enum\YesNoEnum;

class YesNoEnumType extends AbstractEnumType
{
    public const NAME = 'yes_no_enum';

    protected function getEnumClass(): string
    {
        return YesNoEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
