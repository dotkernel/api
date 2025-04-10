<?php

declare(strict_types=1);

namespace Api\App\InputFilter\Input;

use Core\App\Message;
use Laminas\Validator\Identical;

class PasswordConfirmInput extends PasswordInput
{
    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getValidatorChain()
            ->attachByName(Identical::class, [
                'token'   => 'password',
                'message' => Message::validatorMismatch('Password', 'Confirm password'),
            ], true);
    }
}
