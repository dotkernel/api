<?php

declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;

return [
    ConfigurationInterface::CONFIGURATION_IDENTIFIER => [
        'allowed_origins' => [ConfigurationInterface::ANY_ORIGIN], // Allow any origin
        'allowed_headers' => ['Accept', 'Content-Type', 'Authorization'], // Custom headers
        'allowed_max_age' => '600', // 10 minutes
        'credentials_allowed' => true, // Allow cookies
        'exposed_headers' => [], // Tell client that the API will always return this header
    ],
];
