<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\User\InputFilter\Input\EmailInput;
use Api\User\InputFilter\Input\IdentityInput;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class ResetPasswordInputFilter extends InputFilter
{
    public function __construct()
    {
        $this
            ->add(new EmailInput('email', false))
            ->add(new IdentityInput('identity', false));
    }
}
