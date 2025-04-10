<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\IdentityInput;
use Api\App\InputFilter\Input\PasswordConfirmInput;
use Api\App\InputFilter\Input\PasswordInput;
use Api\User\InputFilter\Input\StatusInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\CollectionInputFilter;

class CreateUserInputFilter extends AbstractInputFilter
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
