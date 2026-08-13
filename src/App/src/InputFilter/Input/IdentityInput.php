<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

class IdentityInput extends Input
{
    public const int IDENTITY_MIN_LENGTH = 3;
    public const int IDENTITY_MAX_LENGTH = 100;

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
                'min'     => self::IDENTITY_MIN_LENGTH,
                'max'     => self::IDENTITY_MAX_LENGTH,
                'message' => Message::validatorLengthMinMax(self::IDENTITY_MIN_LENGTH, self::IDENTITY_MAX_LENGTH),
            ], true);
    }
}
