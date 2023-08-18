<?php

declare(strict_types=1);

namespace Api\User\InputFilter\Input;

use Api\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\Identical;

class PasswordConfirmInput extends Input
{
    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(Identical::class, [
                'token'   => 'password',
                'message' => Message::VALIDATOR_PASSWORD_MISMATCH,
            ], true);
    }
}
