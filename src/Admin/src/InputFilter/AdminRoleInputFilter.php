<?php

declare(strict_types=1);

namespace Api\Admin\InputFilter;

use Api\App\InputFilter\Input\UuidInput;
use Core\App\InputFilter\AbstractInputFilter;

/**
 * @phpstan-type AdminRoleDataType array{
 *     id: non-empty-string,
 * }
 * @extends AbstractInputFilter<AdminRoleDataType>
 */
class AdminRoleInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $this->add(new UuidInput('id'));
    }
}
