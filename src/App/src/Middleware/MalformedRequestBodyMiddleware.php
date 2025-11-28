<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\RuntimeException;
use Mezzio\Helper\Exception\MalformedRequestBodyException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class MalformedRequestBodyMiddleware implements MiddlewareInterface
{
    /**
     * @throws BadRequestException
     * @throws RuntimeException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (MalformedRequestBodyException $exception) {
            throw BadRequestException::create(
                $exception->getMessage() ?: 'Error when parsing JSON request body: Syntax error'
            );
        }
    }
}
