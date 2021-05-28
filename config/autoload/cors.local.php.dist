<?php

declare(strict_types=1);

use Mezzio\Cors\Configuration\ConfigurationInterface;

return [
    ConfigurationInterface::CONFIGURATION_IDENTIFIER => [
        'allowed_origins' => [
            /**
             * Leaving this line here makes your application accessible by any origin.
             *
             * To restrict, replace this line with a list of origins that should have access to your application.
             * Example: "domain1.com", "domain2.com"
             */
            ConfigurationInterface::ANY_ORIGIN
        ],
        'allowed_headers' => ['Accept', 'Content-Type', 'Authorization'], // Custom headers
        'allowed_max_age' => '600', // 10 minutes
        'credentials_allowed' => true, // Allow cookies
        'exposed_headers' => [], // Tell client that the API will always return this header
    ],
];
