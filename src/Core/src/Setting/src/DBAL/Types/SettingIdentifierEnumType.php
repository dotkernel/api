<?php

declare(strict_types=1);

namespace Core\Setting\DBAL\Types;

use Core\App\DBAL\Types\AbstractEnumType;
use Core\Setting\Enum\SettingIdentifierEnum;

class SettingIdentifierEnumType extends AbstractEnumType
{
    public const NAME = 'setting_enum';

    protected function getEnumClass(): string
    {
        return SettingIdentifierEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
