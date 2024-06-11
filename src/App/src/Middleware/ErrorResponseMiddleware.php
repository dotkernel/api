<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Dot\DependencyInjection\Attribute\Inject;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function json_decode;
use function json_encode;

class ErrorResponseMiddleware implements MiddlewareInterface
{
    #[Inject(
        "config.authentication",
    )]
    public function __construct(
        protected array $config,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response->getStatusCode() === StatusCodeInterface::STATUS_BAD_REQUEST) {
            $body = json_decode((string) $response->getBody());
            if ($body->error === 'invalid_grant' && empty($body->hint)) {
                $body->error             = $this->config['invalid_credentials']['error'];
                $body->error_description = $this->config['invalid_credentials']['error_description'];
                $body->message           = $this->config['invalid_credentials']['message'];

                $stream = new Stream('php://temp', 'wb+');
                $stream->write(json_encode($body));

                return $response->withBody($stream);
            }
        }

        return $response;
    }
}
