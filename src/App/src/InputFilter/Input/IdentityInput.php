<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

/**
 * @phpstan-import-type InputSpecification from InputFilterInterface
 */
final class IdentityInput
{
    public const int IDENTITY_MIN_LENGTH = 3;
    public const int IDENTITY_MAX_LENGTH = 100;

    private function __construct()
    {
    }

    /**
     * @param non-empty-string $name
     * @return InputSpecification
     */
    public static function spec(string $name, bool $isRequired = true): array
    {
        $lengthMessage = Message::validatorLengthMinMax(self::IDENTITY_MIN_LENGTH, self::IDENTITY_MAX_LENGTH);

        return [
            'name'       => $name,
            'required'   => $isRequired,
            'filters'    => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class],
            ],
            'validators' => [
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
                        'min'      => self::IDENTITY_MIN_LENGTH,
                        'max'      => self::IDENTITY_MAX_LENGTH,
                        'messages' => [
                            StringLength::INVALID   => $lengthMessage,
                            StringLength::TOO_SHORT => $lengthMessage,
                            StringLength::TOO_LONG  => $lengthMessage,
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
            ],
        ];
    }
}
