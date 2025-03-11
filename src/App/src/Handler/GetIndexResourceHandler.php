<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Attribute\ResourceDeprecation;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[ResourceDeprecation(
    sunset: '2038-01-01',
    link: 'https://docs.dotkernel.org/api-documentation/v5/core-features/versioning',
    deprecationReason: 'Resource deprecation example.',
)]
class GetIndexResourceHandler extends AbstractHandler
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->jsonResponse(['message' => 'Dotkernel API version 5']);
    }
}
