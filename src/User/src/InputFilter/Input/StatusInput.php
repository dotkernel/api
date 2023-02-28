<?php

declare(strict_types=1);

namespace Api\User\InputFilter\Input;

use Api\App\Message;
use Api\User\Entity\User;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\InArray;

class StatusInput extends Input
{
    public function __construct(string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(InArray::class, [
                'haystack' => User::STATUSES,
                'message' => sprintf(Message::INVALID_VALUE, 'status'),
            ], true);
    }
}
