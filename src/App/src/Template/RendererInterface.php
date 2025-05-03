<?php

declare(strict_types=1);

namespace Api\App\Template;

interface RendererInterface
{
    public function render(string $template, array $params = []): false|string;
}
