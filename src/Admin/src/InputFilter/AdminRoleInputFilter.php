<?php

declare(strict_types=1);

namespace Api\Admin\InputFilter;

use Api\Admin\InputFilter\Input\UuidInput;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class AdminRoleInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(new UuidInput('uuid'));
    }
}
