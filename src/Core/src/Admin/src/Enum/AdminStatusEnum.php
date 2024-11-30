<?php

declare(strict_types=1);

namespace Core\Admin\Enum;

enum AdminStatusEnum: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
}
