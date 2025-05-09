<?php

declare(strict_types=1);

namespace Api\App\Template;

interface RendererInterface
{
    /**
     * @param non-empty-string $template
     * @param array<non-empty-string, mixed> $params
     */
    public function render(string $template, array $params = []): string;
}
