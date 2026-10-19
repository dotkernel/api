<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\IdentityInput;
use Api\App\InputFilter\Input\PasswordConfirmInput;
use Api\App\InputFilter\Input\PasswordInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\Factory;

/**
 * @phpstan-import-type CreateUserDetailDataType from CreateUserDetailInputFilter
 * @phpstan-type CreateUserAccountDataType array{
 *     identity: non-empty-string,
 *     password: non-empty-string,
 *     passwordConfirm: non-empty-string,
 *     detail: CreateUserDetailDataType,
 * }
 * @extends AbstractInputFilter<CreateUserAccountDataType>
 */
class CreateUserAccountInputFilter extends AbstractInputFilter
{
    public function __construct(Factory $factory)
    {
        parent::__construct($factory);

        $this
            ->add(IdentityInput::spec('identity'))
            ->add(PasswordInput::spec('password'))
            ->add(PasswordConfirmInput::spec('passwordConfirm'))
            ->add(new CreateUserDetailInputFilter($factory), 'detail');
    }
}
