<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\PasswordConfirmInput;
use Api\App\InputFilter\Input\PasswordInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\Factory;

/**
 * @phpstan-import-type UpdateUserDetailDataType from UpdateUserDetailInputFilter
 * @phpstan-type UpdateUserAccountDataType array{
 *     password?: non-empty-string,
 *     passwordConfirm?: non-empty-string,
 *     detail: UpdateUserDetailDataType,
 * }
 * @extends AbstractInputFilter<UpdateUserAccountDataType>
 */
class UpdateUserAccountInputFilter extends AbstractInputFilter
{
    public function __construct(Factory $factory)
    {
        parent::__construct($factory);

        $this
            ->add(PasswordInput::spec('password', false))
            ->add(PasswordConfirmInput::spec('passwordConfirm', false))
            ->add(new UpdateUserDetailInputFilter($factory), 'detail');
    }
}
