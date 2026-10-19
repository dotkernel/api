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
use Laminas\InputFilter\Factory;

/**
 * @phpstan-import-type AdminRoleDataType from AdminRoleInputFilter
 * @phpstan-type CreateAdminDataType array{
 *     identity: non-empty-string,
 *     password: non-empty-string,
 *     passwordConfirm: non-empty-string,
 *     firstName?: non-empty-string,
 *     lastName?: non-empty-string,
 *     status: non-empty-string,
 *     roles: AdminRoleDataType[],
 * }
 * @extends AbstractInputFilter<CreateAdminDataType>
 */
class CreateAdminInputFilter extends AbstractInputFilter
{
    public function __construct(Factory $factory)
    {
        parent::__construct($factory);

        $roles = [
            'type'         => CollectionInputFilter::class,
            'input_filter' => new AdminRoleInputFilter($factory),
            'required'     => true,
        ];

        $this
            ->add(IdentityInput::spec('identity'))
            ->add(PasswordInput::spec('password'))
            ->add(PasswordConfirmInput::spec('passwordConfirm'))
            ->add(FirstNameInput::spec('firstName', false))
            ->add(LastNameInput::spec('lastName', false))
            ->add(StatusInput::spec('status'))
            ->add($roles, 'roles');
    }
}
