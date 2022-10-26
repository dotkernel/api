<?php

namespace AppTest\Functional\Exception;

use Fig\Http\Message\StatusCodeInterface;
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

    public static function invalidResponse(string $key): self
    {
        return new self(
            sprintf('The `%s` key is missing from the response', $key),
        StatusCodeInterface::STATUS_BAD_REQUEST
        );
    }
}
