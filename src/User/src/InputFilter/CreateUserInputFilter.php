<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\IdentityInput;
use Api\App\InputFilter\Input\PasswordConfirmInput;
use Api\App\InputFilter\Input\PasswordInput;
use Api\User\InputFilter\Input\StatusInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\CollectionInputFilter;

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
