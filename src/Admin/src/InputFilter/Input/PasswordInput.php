<?php

declare(strict_types=1);

namespace Api\Admin\InputFilter\Input;

use Api\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

use function sprintf;

class PasswordInput extends Input
{
    public const PASSWORD_MIN_LENGTH = 6;

    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(StringLength::class, [
                'min'     => self::PASSWORD_MIN_LENGTH,
                'message' => sprintf(Message::VALIDATOR_MIN_LENGTH, 'Password', self::PASSWORD_MIN_LENGTH),
            ], true)
            ->attachByName(NotEmpty::class, [
                'message' => sprintf(Message::VALIDATOR_REQUIRED_FIELD_BY_NAME, 'Password'),
            ], true);
    }
}
