<?php

declare(strict_types=1);

namespace Api\App\Exception;

use DomainException;
use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

/**
 * Class RuntimeException
 * @package Api\App\Exception
 */
class RuntimeException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    /**
     * @param string $message
     * @return static
     */
    public static function create(string $message): self
    {
        $e = new self($message);
        $e->status = 500;
        $e->detail = $message;
        $e->type = '/api/doc/runtime-error';
        $e->title = 'Runtime error, please contact the administrator';
        return $e;
    }
}
