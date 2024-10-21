<?php

declare(strict_types=1);

namespace Api\App\Exception;

use Api\App\Message;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class ExpiredException extends Exception implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    public static function create(string $detail, string $type = '', array $additional = []): self
    {
        $exception = new self();

        $exception->type       = $type;
        $exception->detail     = $detail;
        $exception->status     = StatusCodeInterface::STATUS_GONE;
        $exception->title      = Message::EXPIRED;
        $exception->additional = $additional;

        return $exception;
    }
}
