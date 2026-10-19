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
 * @phpstan-import-type UpdateUserDetailDataType from UpdateUserDetailInputFilter
 * @phpstan-import-type UserRoleDataType from UserRoleInputFilter
 * @phpstan-type CreateAdminDataType array{
 *     identity: non-empty-string,
 *     password: non-empty-string,
 *     passwordConfirm: non-empty-string,
 *     status: non-empty-string,
 *     detail: UpdateUserDetailDataType,
 *     roles: UserRoleDataType[],
 * }
 * @extends AbstractInputFilter<CreateAdminDataType>
 */
class UpdateUserInputFilter extends AbstractInputFilter
{
    public function __construct(Factory $factory)
    {
        parent::__construct($factory);

        $roles = [
            'type'         => CollectionInputFilter::class,
            'input_filter' => new UserRoleInputFilter($factory),
            'required'     => false,
        ];

        $this
            ->add(IdentityInput::spec('identity', false))
            ->add(PasswordInput::spec('password', false))
            ->add(PasswordConfirmInput::spec('passwordConfirm', false))
            ->add(StatusInput::spec('status', false))
            ->add(new UpdateUserDetailInputFilter($factory), 'detail')
            ->add($roles, 'roles');
    }
}
