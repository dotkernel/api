<?php

declare(strict_types=1);

namespace Api\Admin\InputFilter;

use Api\Admin\InputFilter\Input\StatusInput;
use Api\App\InputFilter\Input\FirstNameInput;
use Api\App\InputFilter\Input\IdentityInput;
use Api\App\InputFilter\Input\LastNameInput;
use Api\App\InputFilter\Input\PasswordConfirmInput;
use Api\App\InputFilter\Input\PasswordInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\CollectionInputFilter;

class CreateAdminInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $roles = (new CollectionInputFilter())
            ->setInputFilter(new AdminRoleInputFilter())
            ->setIsRequired(true);

        $this
            ->add(new IdentityInput('identity'))
            ->add(new PasswordInput('password'))
            ->add(new PasswordConfirmInput('passwordConfirm'))
            ->add(new FirstNameInput('firstName', false))
            ->add(new LastNameInput('lastName', false))
            ->add(new StatusInput('status'))
            ->add($roles, 'roles');
    }
}
