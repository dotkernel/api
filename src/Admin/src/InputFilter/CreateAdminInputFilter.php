<?php

declare(strict_types=1);

namespace Api\Admin\InputFilter;

use Api\Admin\InputFilter\Input\FirstNameInput;
use Api\Admin\InputFilter\Input\IdentityInput;
use Api\Admin\InputFilter\Input\LastNameInput;
use Api\Admin\InputFilter\Input\PasswordConfirmInput;
use Api\Admin\InputFilter\Input\PasswordInput;
use Api\Admin\InputFilter\Input\StatusInput;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class CreateAdminInputFilter extends InputFilter
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
            ->add(new FirstNameInput('firstName'))
            ->add(new LastNameInput('lastName'))
            ->add(new StatusInput('status', false))
            ->add($roles, 'roles');
    }
}
