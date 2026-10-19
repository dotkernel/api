<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Hostname;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

/**
 * @phpstan-import-type InputSpecification from InputFilterInterface
 */
final class EmailInput
{
    public const int EMAIL_MAX_LENGTH = 191;

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
                    'name'                   => EmailAddress::class,
                    'options'                => [
                        'messages' => [
                            EmailAddress::INVALID             => Message::VALIDATOR_INVALID_EMAIL,
                            EmailAddress::INVALID_FORMAT      => Message::VALIDATOR_INVALID_EMAIL,
                            EmailAddress::INVALID_HOSTNAME    => Message::VALIDATOR_INVALID_EMAIL,
                            EmailAddress::DOT_ATOM            => Message::VALIDATOR_INVALID_EMAIL,
                            EmailAddress::QUOTED_STRING       => Message::VALIDATOR_INVALID_EMAIL,
                            EmailAddress::INVALID_LOCAL_PART  => Message::VALIDATOR_INVALID_EMAIL,
                            EmailAddress::LENGTH_EXCEEDED     => Message::VALIDATOR_INVALID_EMAIL,
                            Hostname::CANNOT_DECODE_PUNYCODE  => Message::VALIDATOR_INVALID_EMAIL,
                            Hostname::INVALID_DASH            => Message::VALIDATOR_INVALID_EMAIL,
                            Hostname::INVALID_HOSTNAME        => Message::VALIDATOR_INVALID_EMAIL,
                            Hostname::INVALID_HOSTNAME_SCHEMA => Message::VALIDATOR_INVALID_EMAIL,
                            Hostname::INVALID_LOCAL_NAME      => Message::VALIDATOR_INVALID_EMAIL,
                            Hostname::IP_ADDRESS_NOT_ALLOWED  => Message::VALIDATOR_INVALID_EMAIL,
                            Hostname::LOCAL_NAME_NOT_ALLOWED  => Message::VALIDATOR_INVALID_EMAIL,
                            Hostname::UNDECIPHERABLE_TLD      => Message::VALIDATOR_INVALID_EMAIL,
                            Hostname::UNKNOWN_TLD             => Message::VALIDATOR_INVALID_EMAIL,
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name'                   => StringLength::class,
                    'options'                => [
                        'max'      => self::EMAIL_MAX_LENGTH,
                        'messages' => [
                            StringLength::TOO_LONG => Message::validatorLengthMax(self::EMAIL_MAX_LENGTH),
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
            ],
        ];
    }
}
