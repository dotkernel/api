<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\EmailInput;
use Core\App\InputFilter\AbstractInputFilter;

class ActivateAccountInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $this->add(new EmailInput('email'));
    }
}
