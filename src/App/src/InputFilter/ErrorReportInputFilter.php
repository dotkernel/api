<?php

declare(strict_types=1);

namespace Api\App\InputFilter;

use Api\App\InputFilter\Input\MessageInput;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class ErrorReportInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(new MessageInput('message'));
    }
}
