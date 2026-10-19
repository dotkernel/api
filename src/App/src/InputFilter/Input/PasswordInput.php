<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;

/**
 * @phpstan-import-type InputSpecification from InputFilterInterface
 * @phpstan-import-type ValidatorSpecification from ValidatorChain
 */
final class PasswordInput
{
    public const int PASSWORD_MIN_LENGTH = 8;
    public const int PASSWORD_MAX_LENGTH = 150;

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
            'name'       => $name,
            'required'   => $isRequired,
            'filters'    => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class],
            ],
            'validators' => self::validators(),
        ];
    }

    /**
     * @return list<ValidatorSpecification>
     */
    public static function validators(): array
    {
        $lengthMessage = Message::validatorLengthMinMax(self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH);

        return [
            [
                'name'                   => NotEmpty::class,
                'options'                => [
                    'messages' => [
                        NotEmpty::IS_EMPTY => Message::VALIDATOR_REQUIRED_FIELD,
                    ],
                ],
                'break_chain_on_failure' => true,
            ],
            [
                'name'                   => StringLength::class,
                'options'                => [
                    'min'      => self::PASSWORD_MIN_LENGTH,
                    'max'      => self::PASSWORD_MAX_LENGTH,
                    'messages' => [
                        StringLength::INVALID   => $lengthMessage,
                        StringLength::TOO_SHORT => $lengthMessage,
                        StringLength::TOO_LONG  => $lengthMessage,
                    ],
                ],
                'break_chain_on_failure' => true,
            ],
        ];
    }
}
