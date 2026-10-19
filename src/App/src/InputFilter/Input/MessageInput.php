<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\NotEmpty;

/**
 * @phpstan-import-type InputSpecification from InputFilterInterface
 */
final class MessageInput
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
            ],
        ];
    }
}
