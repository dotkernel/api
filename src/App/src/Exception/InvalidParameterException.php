<?php

declare(strict_types=1);

namespace Api\App\Exception;

use DomainException;
use Zend\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Zend\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

/**
 * Class InvalidParameterException
 * @package Api\App\Exception
 */
class InvalidParameterException extends DomainException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    /**
     * @param string $message
     * @param array $additionalData
     * @return static
     */
    public static function create(string $message, array $additionalData = []) : self
    {
        $e = new self($message);
        $e->status = 400;
        $e->detail = $message;
        $e->type = '/api/doc/invalid-parameter';
        $e->title = 'Invalid parameter';
        $e->additional['parameters'] = $additionalData;
        return $e;
    }
}
