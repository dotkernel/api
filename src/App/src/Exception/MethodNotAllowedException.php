<?php

declare(strict_types=1);

namespace Api\App\Exception;

use DomainException;
use Zend\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Zend\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

/**
 * Class MethodNotAllowedException
 * @package Api\App\Exception
 */
class MethodNotAllowedException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    /**
     * @param string $message
     * @return static
     */
    public static function create(string $message) : self
    {
        $e = new self($message);
        $e->status = 405;
        $e->detail = $message;
        $e->type = '/api/doc/method-not-allowed-error';
        $e->title = 'Method is not allowed';
        return $e;
    }
}
