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
class UpdateUserInputFilter extends InputFilter
{
    public function __construct()
    {
        $roles = (new CollectionInputFilter())
            ->setInputFilter(new UserRoleInputFilter())
            ->setIsRequired(false);

        $this
            ->add(new IdentityInput('identity', false))
            ->add(new PasswordInput('password', false))
            ->add(new PasswordConfirmInput('passwordConfirm', false))
            ->add(new StatusInput('status', false))
            ->add(new UpdateUserDetailInputFilter(), 'detail')
            ->add($roles, 'roles');
    }
}
