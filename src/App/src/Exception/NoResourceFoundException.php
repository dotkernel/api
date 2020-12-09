<?php

declare(strict_types=1);

namespace Api\App\Exception;

use DomainException;
use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

/**
 * Class NoResourceFoundException
 * @package Api\App\Exception
 */
class NoResourceFoundException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    /**
     * @param string $message
     * @return static
     */
    public static function create(string $message): self
    {
        $e = new self($message);
        $e->status = 404;
        $e->detail = $message;
        $e->type = '/api/doc/resource-not-found';
        $e->title = 'Resource not found';
        return $e;
    }
}
