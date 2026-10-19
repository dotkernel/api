<?php

declare(strict_types=1);

namespace Api\App\InputFilter;

use Api\App\InputFilter\Input\MessageInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\Factory;

/**
 * @phpstan-type ErrorReportDataType array{
 *     message: non-empty-string,
 * }
 * @extends AbstractInputFilter<ErrorReportDataType>
 */
class ErrorReportInputFilter extends AbstractInputFilter
{
    public function __construct(Factory $factory)
    {
        parent::__construct($factory);

        $this->add(MessageInput::spec('message'));
    }
}
