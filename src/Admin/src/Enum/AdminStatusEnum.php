<?php

declare(strict_types=1);

namespace Api\Admin\Enum;

enum AdminStatusEnum: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
}
