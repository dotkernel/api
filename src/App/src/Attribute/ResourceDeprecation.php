<?php

declare(strict_types=1);

namespace Api\App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ResourceDeprecation extends BaseDeprecation
{
}
