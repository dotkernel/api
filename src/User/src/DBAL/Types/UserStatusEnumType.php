<?php

declare(strict_types=1);

namespace Api\User\DBAL\Types;

use Api\App\DBAL\Types\AbstractEnumType;
use Api\User\Enum\UserStatusEnum;

class UserStatusEnumType extends AbstractEnumType
{
    public const NAME = 'user_status_enum';

    protected function getEnumClass(): string
    {
        return UserStatusEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
