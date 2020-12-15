<?php


namespace Api\App\Common\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Stream;

class ErrorResponseMiddleware implements MiddlewareInterface
{
    public const STATUS_400 = 400;
    public const INVALID_GRANT = 'invalid_grant';

    /** @var array $config */
    private array $config;

    /**
     * ErrorResponseMiddleware constructor.
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
                $body->error_description = $this->config['invalid_credentials']['error'];
                $body->message = $this->config['invalid_credentials']['message'];
                $stream = new Stream('php://temp', 'wb+');
                $stream->write(json_encode($body));
                return $response->withBody($stream);
            }
        }
        return $response;
    }
}