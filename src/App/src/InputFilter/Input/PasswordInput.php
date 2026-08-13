<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

class PasswordInput extends Input
{
    public const int PASSWORD_MIN_LENGTH = 8;
    public const int PASSWORD_MAX_LENGTH = 150;

    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(NotEmpty::class, [
                'message' => Message::VALIDATOR_REQUIRED_FIELD,
            ], true)
            ->attachByName(StringLength::class, [
                'min'     => self::PASSWORD_MIN_LENGTH,
                'max'     => self::PASSWORD_MAX_LENGTH,
                'message' => Message::validatorLengthMinMax(self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH),
            ], true);
    }
}
