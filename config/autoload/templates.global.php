<?php

declare(strict_types=1);

use Api\App\Template\RendererInterface;

return [
    RendererInterface::class => [
        'globals'   => [],
        'extension' => 'phtml',
    ],
];
