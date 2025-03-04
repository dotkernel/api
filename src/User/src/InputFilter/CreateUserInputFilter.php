<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\User\InputFilter\Input\IdentityInput;
use Api\User\InputFilter\Input\PasswordConfirmInput;
use Api\User\InputFilter\Input\PasswordInput;
use Api\User\InputFilter\Input\StatusInput;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class CreateUserInputFilter extends InputFilter
{
    public function __construct()
    {
        $roles = (new CollectionInputFilter())
            ->setInputFilter(new UserRoleInputFilter())
            ->setIsRequired(true);

        $this
            ->add(new IdentityInput('identity'))
            ->add(new PasswordInput('password'))
            ->add(new PasswordConfirmInput('passwordConfirm'))
            ->add(new StatusInput('status', false))
            ->add(new CreateUserDetailInputFilter(), 'detail')
            ->add($roles, 'roles');
    }
}
