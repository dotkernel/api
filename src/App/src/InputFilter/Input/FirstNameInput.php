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
final class FirstNameInput
{
    public const int FIRSTNAME_MAX_LENGTH = 191;

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
                        'max'      => self::FIRSTNAME_MAX_LENGTH,
                        'messages' => [
                            StringLength::INVALID  => Message::validatorLengthMax(self::FIRSTNAME_MAX_LENGTH),
                            StringLength::TOO_LONG => Message::validatorLengthMax(self::FIRSTNAME_MAX_LENGTH),
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
            ],
        ];
    }
}
