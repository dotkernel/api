<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\EmailInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\Factory;

/**
 * @phpstan-type RecoverIdentityDataType array{
 *     email: non-empty-string,
 * }
 * @extends AbstractInputFilter<RecoverIdentityDataType>
 */
class RecoverIdentityInputFilter extends AbstractInputFilter
{
    public function __construct(Factory $factory)
    {
        parent::__construct($factory);

        $this->add(EmailInput::spec('email'));
    }
}
