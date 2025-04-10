<?php

declare(strict_types=1);

namespace Core\App\DBAL\Types;

use Core\App\Enum\SuccessFailureEnum;

class SuccessFailureEnumType extends AbstractEnumType
{
    public const NAME = 'success_failure_enum';

    protected function getEnumClass(): string
    {
        return SuccessFailureEnum::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
