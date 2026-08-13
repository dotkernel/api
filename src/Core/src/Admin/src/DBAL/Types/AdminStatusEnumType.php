<?php

declare(strict_types=1);

namespace Core\Admin\DBAL\Types;

use Core\Admin\Enum\AdminStatusEnum;
use Core\App\DBAL\Types\AbstractEnumType;

class AdminStatusEnumType extends AbstractEnumType
{
    public const string NAME = 'admin_status_enum';

    public function getEnumClass(): string
    {
        return AdminStatusEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
