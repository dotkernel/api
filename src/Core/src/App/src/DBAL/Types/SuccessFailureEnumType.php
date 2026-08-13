<?php

declare(strict_types=1);

namespace Core\App\DBAL\Types;

use Core\App\Enum\SuccessFailureEnum;

class SuccessFailureEnumType extends AbstractEnumType
{
    public const string NAME = 'success_failure_enum';

    public function getEnumClass(): string
    {
        return SuccessFailureEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
