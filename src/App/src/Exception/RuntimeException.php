<?php

declare(strict_types=1);

namespace Api\App\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class RuntimeException extends \RuntimeException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    /**
     * @param non-empty-string $detail
     * @param array<string, mixed> $additional
     */
    public static function create(string $detail, string $type = '', string $title = '', array $additional = []): self
    {
        $exception = new self();

        $exception->type       = $type;
        $exception->detail     = $detail;
        $exception->status     = StatusCodeInterface::STATUS_BAD_REQUEST;
        $exception->title      = $title;
        $exception->additional = $additional;

        return $exception;
    }
}
