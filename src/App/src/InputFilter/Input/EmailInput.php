<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

class EmailInput extends Input
{
    public const EMAIL_MAX_LENGTH = 191;

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
            ->attachByName(EmailAddress::class, [
                'message' => Message::VALIDATOR_INVALID_EMAIL,
            ], true)
            ->attachByName(StringLength::class, [
                'max'     => self::EMAIL_MAX_LENGTH,
                'message' => Message::validatorLengthMax(self::EMAIL_MAX_LENGTH),
            ], true);
    }
}
