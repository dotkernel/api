<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\IdentityInput;
use Api\App\InputFilter\Input\PasswordConfirmInput;
use Api\App\InputFilter\Input\PasswordInput;
use Api\User\InputFilter\Input\StatusInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\CollectionInputFilter;

class UpdateUserInputFilter extends AbstractInputFilter
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
