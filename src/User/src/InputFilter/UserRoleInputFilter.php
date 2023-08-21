<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\User\InputFilter\Input\UuidInput;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class UserRoleInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(new UuidInput('uuid'));
    }
}
