<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\User\InputFilter\Input\EmailInput;
use Api\User\InputFilter\Input\FirstNameInput;
use Api\User\InputFilter\Input\LastNameInput;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class UpdateUserDetailInputFilter extends InputFilter
{
    public function __construct()
    {
        $this
            ->add(new FirstNameInput('firstName', false))
            ->add(new LastNameInput('lastName', false))
            ->add(new EmailInput('email', false));
    }
}
