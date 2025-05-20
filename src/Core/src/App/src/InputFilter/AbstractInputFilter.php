<?php

declare(strict_types=1);

namespace Core\App\InputFilter;

use Laminas\InputFilter\InputFilter;

/**
 * @template TFilteredValues
 * @extends InputFilter<TFilteredValues>
 */
abstract class AbstractInputFilter extends InputFilter
{
}
