<?php

declare(strict_types=1);

namespace Api\App\Middleware;

use Api\App\Exception\NotAcceptableException;
use Api\App\Exception\UnsupportedMediaTypeException;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function count;
use function explode;
use function in_array;
use function is_array;
use function preg_match;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function strpos;
use function substr;
use function trim;
use function usort;

class ContentNegotiationMiddleware implements MiddlewareInterface
{
    public const DEFAULT_HEADERS = 'default';

    #[Inject(
        'config.content-negotiation',
    )]
    public function __construct(
        private readonly array $config,
    ) {
    }

    /**
     * @throws NotAcceptableException
     * @throws UnsupportedMediaTypeException
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $routeResult = $request->getAttribute(RouteResult::class);
        if (! $routeResult instanceof RouteResult || $routeResult->isFailure()) {
            return $handler->handle($request);
        }

        $routeName = (string) $routeResult->getMatchedRouteName();

        // Parse Accept header including quality values
        $acceptedTypes = $this->parseAcceptHeader($request->getHeaderLine('Accept'));
        if (count($acceptedTypes) === 0) {
            // If no Accept header is provided, assume a wildcard
            $acceptedTypes = [['mediaType' => '*/*', 'quality' => 1.0]];
        }

        $supportedTypes = $this->getConfiguredTypes($routeName, 'Accept');
        if (! $this->isAcceptable($acceptedTypes, $supportedTypes)) {
            throw NotAcceptableException::create(Message::notAcceptable($supportedTypes));
        }

        $contentTypeHeader = $request->getHeaderLine('Content-Type');
        if (! empty($contentTypeHeader)) {
            $contentType            = $this->parseContentTypeHeader($contentTypeHeader);
            $acceptableContentTypes = $this->getConfiguredTypes($routeName, 'Content-Type');
            if (! $this->isContentTypeSupported($contentType, $acceptableContentTypes)) {
                throw UnsupportedMediaTypeException::create(Message::unsupportedMediaType($acceptableContentTypes));
            }
        }

        $response = $handler->handle($request);
        if (! $this->isResponseContentTypeValid($response->getHeaderLine('Content-Type'), $acceptedTypes)) {
            throw NotAcceptableException::create('Unable to provide content in any of the accepted formats.');
        }

        return $response;
    }

    private function parseAcceptHeader(string $header): array
    {
        if (empty($header)) {
            return [];
        }

        $types = [];
        $parts = explode(',', $header);

        foreach ($parts as $part) {
            $part      = trim($part);
            $quality   = 1.0;
            $mediaType = $part;
            if (str_contains($part, ';')) {
                [$mediaType, $parameters] = explode(';', $part, 2);

                $mediaType = trim($mediaType);

                // Extract quality value if present
                if (preg_match('/q=([0-9]*\.?[0-9]+)/', $parameters, $matches)) {
                    $quality = (float) $matches[1];
                }
            }

            // Skip empty media types
            if (empty($mediaType)) {
                continue;
            }

            $types[] = [
                'mediaType' => $mediaType,
                'quality'   => $quality,
            ];
        }

        // Sort by quality in descending order
        usort($types, fn ($a, $b) => $b['quality'] <=> $a['quality']);

        return $types;
    }

    private function parseContentTypeHeader(string $header): array
    {
        if (empty($header)) {
            return [];
        }

        $parts = explode(';', $header);

        $params = [];
        for ($i = 1; $i < count($parts); $i++) {
            $paramParts = explode('=', $parts[$i], 2);
            if (count($paramParts) === 2) {
                $params[trim($paramParts[0])] = trim($paramParts[1], ' "\'');
            }
        }

        return [
            'mediaType'  => trim($parts[0]),
            'parameters' => $params,
        ];
    }

    private function getConfiguredTypes(string $routeName, string $headerType): array
    {
        $types = $this->config[self::DEFAULT_HEADERS][$headerType] ?? [];
        if (! empty($this->config[$routeName][$headerType])) {
            $types = $this->config[$routeName][$headerType];
        }

        return is_array($types) ? $types : [$types];
    }

    private function isAcceptable(array $acceptedTypes, array $supportedTypes): bool
    {
        foreach ($acceptedTypes as $accept) {
            // Wildcard accept
            if ($accept['mediaType'] === '*/*') {
                return true;
            }

            // Type a wildcard like image/*
            if (str_ends_with($accept['mediaType'], '/*')) {
                $prefix = substr($accept['mediaType'], 0, strpos($accept['mediaType'], '/*'));
                foreach ($supportedTypes as $supported) {
                    if (str_starts_with($supported, $prefix . '/')) {
                        return true;
                    }
                }
            }

            // Direct match
            if (in_array($accept['mediaType'], $supportedTypes, true)) {
                return true;
            }
        }

        return false;
    }

    private function isContentTypeSupported(array $contentType, array $supportedTypes): bool
    {
        if (empty($contentType)) {
            return true;
        }

        return in_array($contentType['mediaType'], $supportedTypes, true);
    }

    private function isResponseContentTypeValid(?string $responseType, array $acceptedTypes): bool
    {
        if (empty($responseType)) {
            return true;
        }

        // Parse response content type to handle parameters
        $parts     = explode(';', $responseType);
        $mediaType = trim($parts[0]);

        // Check for wildcard accept
        foreach ($acceptedTypes as $accept) {
            if ($accept['mediaType'] === '*/*') {
                return true;
            }

            // Type a wildcard like image/*
            if (str_ends_with($accept['mediaType'], '/*')) {
                $prefix = substr($accept['mediaType'], 0, strpos($accept['mediaType'], '/*'));
                if (str_starts_with($mediaType, $prefix . '/')) {
                    return true;
                }
            }

            // Handle +json suffix matching
            if (str_ends_with($mediaType, '+json') && str_ends_with($accept['mediaType'], '+json')) {
                return true;
            }

            // Direct match
            if ($mediaType === $accept['mediaType']) {
                return true;
            }
        }

        return false;
    }

    public function notAcceptableResponse(string $message): ResponseInterface
    {
        return new JsonResponse(['messages' => [$message]], StatusCodeInterface::STATUS_NOT_ACCEPTABLE);
    }

    public function unsupportedMediaTypeResponse(string $message): ResponseInterface
    {
        return new JsonResponse(['messages' => [$message]], StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE);
    }
}
