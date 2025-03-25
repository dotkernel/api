<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Attribute\ResourceDeprecation;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

#[ResourceDeprecation(
    sunset: '2038-01-01',
    link: 'https://docs.dotkernel.org/api-documentation/v5/core-features/versioning',
    deprecationReason: 'Resource deprecation example.',
)]
class GetIndexResourceHandler extends AbstractHandler
{
    #[Inject(
        'config.application.name',
    )]
    public function __construct(
        private readonly string $applicationName,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->jsonResponse(['message' => sprintf('%s version 5', $this->applicationName)]);
    }
}
