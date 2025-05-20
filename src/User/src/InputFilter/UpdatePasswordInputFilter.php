<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\PasswordConfirmInput;
use Api\App\InputFilter\Input\PasswordInput;
use Core\App\InputFilter\AbstractInputFilter;

/**
 * @phpstan-type UpdatePasswordDataType array{
 *     password: non-empty-string,
 *     passwordConfirm: non-empty-string,
 * }
 * @extends AbstractInputFilter<UpdatePasswordDataType>
 */
class UpdatePasswordInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $this
            ->add(new PasswordInput('password'))
            ->add(new PasswordConfirmInput('passwordConfirm'));
    }
}
