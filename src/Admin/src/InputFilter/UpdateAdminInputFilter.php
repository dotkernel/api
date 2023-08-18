<?php

declare(strict_types=1);

namespace Api\Admin\InputFilter;

use Api\Admin\InputFilter\Input\FirstNameInput;
use Api\Admin\InputFilter\Input\LastNameInput;
use Api\Admin\InputFilter\Input\PasswordConfirmInput;
use Api\Admin\InputFilter\Input\PasswordInput;
use Api\Admin\InputFilter\Input\StatusInput;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class UpdateAdminInputFilter extends InputFilter
{
    public function __construct()
    {
        $roles = (new CollectionInputFilter())
            ->setInputFilter(new AdminRoleInputFilter())
            ->setIsRequired(false);

        $this
            ->add(new PasswordInput('password', false))
            ->add(new PasswordConfirmInput('passwordConfirm', false))
            ->add(new FirstNameInput('firstName', false))
            ->add(new LastNameInput('lastName', false))
            ->add(new StatusInput('status', false))
            ->add($roles, 'roles');
    }
}
