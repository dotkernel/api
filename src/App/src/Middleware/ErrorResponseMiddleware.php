<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Dot\AnnotatedServices\Annotation\Inject;
use Laminas\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class ErrorResponseMiddleware
 * @package Api\App\Middleware
 */
class ErrorResponseMiddleware implements MiddlewareInterface
{
    public const STATUS_400 = 400;
    public const INVALID_GRANT = 'invalid_grant';

    /** @var array $config */
    private array $config;

    /**
     * ErrorResponseMiddleware constructor
     *
     * @Inject({"config.authentication"})
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response->getStatusCode() === self::STATUS_400) {
            $body = json_decode((string) $response->getBody());
            if ($body->error === self::INVALID_GRANT && empty($body->hint)) {
                $body->error = $this->config['invalid_credentials']['error'];
                $body->error_description = $this->config['invalid_credentials']['error_description'];
                $body->message = $this->config['invalid_credentials']['message'];
                $stream = new Stream('php://temp', 'wb+');
                $stream->write(json_encode($body));
                return $response->withBody($stream);
            }
        }
        return $response;
    }
}
