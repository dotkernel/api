<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

class GetIndexResourceHandler extends AbstractHandler
{
    /**
     * @param array<non-empty-string, mixed> $config
     */
    #[Inject(
        'config.application',
    )]
    public function __construct(
        private readonly array $config,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->jsonResponse([
            'message' => sprintf('%s version %s', $this->config['name'], $this->config['version'] ?? 'X'),
        ]);
    }
}
