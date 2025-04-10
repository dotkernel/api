<?php

declare(strict_types=1);

namespace Core\User\DBAL\Types;

use Core\App\DBAL\Types\AbstractEnumType;
use Core\User\Enum\UserRoleEnum;

class UserRoleEnumType extends AbstractEnumType
{
    public const NAME = 'user_role_enum';

    protected function getEnumClass(): string
    {
        return UserRoleEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
