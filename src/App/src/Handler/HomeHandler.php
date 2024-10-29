<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Api\App\Attribute\ResourceDeprecation;
use Psr\Http\Message\ResponseInterface;

#[ResourceDeprecation(
    sunset: '2038-01-01',
    link: 'https://docs.dotkernel.org/api-documentation/v5/core-features/versioning',
    deprecationReason: 'Resource deprecation example.',
)]
class HomeHandler extends AbstractHandler
{
    public function get(): ResponseInterface
    {
        return $this->jsonResponse(['message' => 'DotKernel API version 5']);
    }
}
