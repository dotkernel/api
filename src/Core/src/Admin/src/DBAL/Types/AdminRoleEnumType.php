<?php

declare(strict_types=1);

namespace Core\Admin\DBAL\Types;

use Core\Admin\Enum\AdminRoleEnum;
use Core\App\DBAL\Types\AbstractEnumType;

class AdminRoleEnumType extends AbstractEnumType
{
    public const string NAME = 'admin_role_enum';

    public function getEnumClass(): string
    {
        return AdminRoleEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
