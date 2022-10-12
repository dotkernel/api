<?php

namespace AppTest\Functional\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Class AuthenticationException
 * @package AppTest\Functional\Exception
 */
class AuthenticationException extends RuntimeException
{
    public static function fromResponse(ResponseInterface $response): self
    {
        return new self($response->getBody()->getContents(), $response->getStatusCode());
    }
}
