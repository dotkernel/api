<?php

declare(strict_types=1);

namespace Core\User\DBAL\Types;

use Core\App\DBAL\Types\AbstractEnumType;
use Core\User\Enum\UserResetPasswordStatusEnum;

class UserResetPasswordStatusEnumType extends AbstractEnumType
{
    public const NAME = 'user_reset_password_status_enum';

    protected function getEnumClass(): string
    {
        return UserResetPasswordStatusEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
