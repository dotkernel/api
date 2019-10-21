<?php

declare(strict_types=1);

namespace Api\App\Exception;

use DomainException;
use Zend\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Zend\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

/**
 * Class OutOfBoundsException
 * @package Api\App\Exception
 */
class OutOfBoundsException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    /**
     * @param string $message
     * @return static
     */
    public static function create(string $message) : self
    {
        $e = new self($message);
        $e->status = 400;
        $e->detail = $message;
        $e->type = '/api/doc/parameter-out-of-range';
        $e->title = 'Parameter out of range';
        return $e;
    }
}
