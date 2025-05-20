<?php

declare(strict_types=1);

namespace Api\Admin\InputFilter;

use Api\Admin\InputFilter\Input\StatusInput;
use Api\App\InputFilter\Input\FirstNameInput;
use Api\App\InputFilter\Input\LastNameInput;
use Api\App\InputFilter\Input\PasswordConfirmInput;
use Api\App\InputFilter\Input\PasswordInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\CollectionInputFilter;

/**
 * @phpstan-import-type AdminRoleDataType from AdminRoleInputFilter
 * @phpstan-type UpdateAdminDataType array{
 *     password?: non-empty-string,
 *     passwordConfirm?: non-empty-string,
 *     firstName?: non-empty-string,
 *     lastName?: non-empty-string,
 *     status?: non-empty-string,
 *     roles?: AdminRoleDataType[],
 * }
 * @extends AbstractInputFilter<UpdateAdminDataType>
 */
class UpdateAdminInputFilter extends AbstractInputFilter
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
