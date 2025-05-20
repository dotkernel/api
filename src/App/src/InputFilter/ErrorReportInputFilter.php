<?php

declare(strict_types=1);

namespace Api\App\InputFilter;

use Api\App\InputFilter\Input\MessageInput;
use Core\App\InputFilter\AbstractInputFilter;

/**
 * @phpstan-type ErrorReportDataType array{
 *     message: non-empty-string,
 * }
 * @extends AbstractInputFilter<ErrorReportDataType>
 */
class ErrorReportInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $this->add(new MessageInput('message'));
    }
}
