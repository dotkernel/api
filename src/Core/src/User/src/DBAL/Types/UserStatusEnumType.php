<?php

declare(strict_types=1);

namespace Core\User\DBAL\Types;

use Core\App\DBAL\Types\AbstractEnumType;
use Core\User\Enum\UserStatusEnum;

class UserStatusEnumType extends AbstractEnumType
{
    public const string NAME = 'user_status_enum';

    public function getEnumClass(): string
    {
        return UserStatusEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
