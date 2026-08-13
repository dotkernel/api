<?php

declare(strict_types=1);

namespace Core\User\DBAL\Types;

use Core\App\DBAL\Types\AbstractEnumType;
use Core\User\Enum\UserResetPasswordStatusEnum;

class UserResetPasswordStatusEnumType extends AbstractEnumType
{
    public const string NAME = 'user_reset_password_status_enum';

    public function getEnumClass(): string
    {
        return UserResetPasswordStatusEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
