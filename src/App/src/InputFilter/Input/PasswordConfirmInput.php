<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\Identical;

/**
 * @phpstan-import-type InputSpecification from InputFilterInterface
 */
final class PasswordConfirmInput
{
    private function __construct()
    {
    }

    /**
     * @param non-empty-string $name
     * @return InputSpecification
     */
    public static function spec(string $name, bool $isRequired = true): array
    {
        return [
            ...PasswordInput::spec($name, $isRequired),
            'validators' => [
                ...PasswordInput::validators(),
                [
                    'name'                   => Identical::class,
                    'options'                => [
                        'token'    => 'password',
                        'messages' => [
                            Identical::NOT_SAME => Message::validatorMismatch('Password', 'Confirm password'),
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
            ],
        ];
    }
}
