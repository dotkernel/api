<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\User\InputFilter\Input\PasswordConfirmInput;
use Api\User\InputFilter\Input\PasswordInput;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class UpdatePasswordInputFilter extends InputFilter
{
    public function __construct()
    {
        $this
            ->add(new PasswordInput('password'))
            ->add(new PasswordConfirmInput('passwordConfirm'));
    }
}
