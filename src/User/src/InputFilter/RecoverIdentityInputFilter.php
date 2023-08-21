<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\User\InputFilter\Input\EmailInput;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class RecoverIdentityInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(new EmailInput('email'));
    }
}
