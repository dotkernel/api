<?php

declare(strict_types=1);

namespace Api\App\Attribute;

interface ResourceInterface
{
    public function hasGuard(): bool;
}
