<?php

declare(strict_types=1);

namespace Api\App\Exception;

use Core\App\InputFilter\ValidationErrors;
use Core\App\Message;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\InputFilter\ValidationResultInterface;
use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class BadRequestException extends Exception implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    /**
     * @param non-empty-string $detail
     * @param array<string, mixed> $additional
     */
    public static function create(string $detail, string $type = '', string $title = '', array $additional = []): self
    {
        $exception = new self($detail);

        $exception->type       = $type;
        $exception->detail     = $detail;
        $exception->status     = StatusCodeInterface::STATUS_BAD_REQUEST;
        $exception->title      = $title;
        $exception->additional = $additional;

        return $exception;
    }

    /**
     * @param ValidationResultInterface<mixed> $result
     */
    public static function fromValidationResult(ValidationResultInterface $result): self
    {
        return self::create(
            detail: Message::VALIDATOR_INVALID_DATA,
            additional: ['errors' => ValidationErrors::fromResult($result)],
        );
    }
}
