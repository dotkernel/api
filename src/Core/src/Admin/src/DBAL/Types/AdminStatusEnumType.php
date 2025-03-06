<?php

declare(strict_types=1);

namespace Core\Admin\DBAL\Types;

use Core\Admin\Enum\AdminStatusEnum;
use Core\App\DBAL\Types\AbstractEnumType;

class AdminStatusEnumType extends AbstractEnumType
{
    public const NAME = 'admin_status_enum';

    protected function getEnumClass(): string
    {
        return AdminStatusEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
