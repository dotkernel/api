<?php

declare(strict_types=1);

namespace Api\App\Exception;

use Exception;

class BadRequestException extends Exception
{
    private array $messages = [];

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(array $messages): static
    {
        $this->messages = $messages;

        return $this;
    }
}
