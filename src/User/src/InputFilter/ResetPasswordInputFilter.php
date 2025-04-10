<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\EmailInput;
use Api\App\InputFilter\Input\IdentityInput;
use Core\App\InputFilter\AbstractInputFilter;

class ResetPasswordInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $this
            ->add(new EmailInput('email', false))
            ->add(new IdentityInput('identity', false));
    }
}
