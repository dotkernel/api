<?php

declare(strict_types=1);

namespace Api\App\InputFilter;

use Api\App\InputFilter\Input\MessageInput;
use Core\App\InputFilter\AbstractInputFilter;

class ErrorReportInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $this->add(new MessageInput('message'));
    }
}
