<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\IdentityInput;
use Api\App\InputFilter\Input\PasswordConfirmInput;
use Api\App\InputFilter\Input\PasswordInput;
use Api\User\InputFilter\Input\StatusInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\Factory;

/**
 * @phpstan-import-type CreateUserDetailDataType from CreateUserDetailInputFilter
 * @phpstan-import-type UserRoleDataType from UserRoleInputFilter
 * @phpstan-type CreateAdminDataType array{
 *     identity: non-empty-string,
 *     password: non-empty-string,
 *     passwordConfirm: non-empty-string,
 *     status: non-empty-string,
 *     detail: CreateUserDetailDataType,
 *     roles: UserRoleDataType[],
 * }
 * @extends AbstractInputFilter<CreateAdminDataType>
 */
class CreateUserInputFilter extends AbstractInputFilter
{
    public function __construct(Factory $factory)
    {
        parent::__construct($factory);

        $roles = [
            'type'         => CollectionInputFilter::class,
            'input_filter' => new UserRoleInputFilter($factory),
            'required'     => true,
        ];

        $this
            ->add(IdentityInput::spec('identity'))
            ->add(PasswordInput::spec('password'))
            ->add(PasswordConfirmInput::spec('passwordConfirm'))
            ->add(StatusInput::spec('status', false))
            ->add(new CreateUserDetailInputFilter($factory), 'detail')
            ->add($roles, 'roles');
    }
}
