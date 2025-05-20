<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\EmailInput;
use Api\App\InputFilter\Input\FirstNameInput;
use Api\App\InputFilter\Input\LastNameInput;
use Core\App\InputFilter\AbstractInputFilter;

/**
 * @phpstan-type UpdateUserDetailDataType array{
 *     firstName?: non-empty-string,
 *     lastName?: non-empty-string,
 *     email?: non-empty-string,
 * }
 * @extends AbstractInputFilter<UpdateUserDetailDataType>
 */
class UpdateUserDetailInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $this
            ->add(new FirstNameInput('firstName', false))
            ->add(new LastNameInput('lastName', false))
            ->add(new EmailInput('email', false));
    }
}
