<?php

declare(strict_types=1);

namespace Api\App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class MethodDeprecation extends BaseDeprecation
{
}
