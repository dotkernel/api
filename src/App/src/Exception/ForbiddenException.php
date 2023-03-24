<?php

declare(strict_types=1);

namespace Api\App\Exception;

use Exception;
use Throwable;

class ForbiddenException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
