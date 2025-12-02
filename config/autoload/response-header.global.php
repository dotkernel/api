<?php

declare(strict_types=1);

return [
    'dot_response_headers' => [
        /**
         * Global headers - applied to all routes
         */
        '*' => [
            'permissions-policy'     => [
                'value'     => 'interest-cohort=()',
                'overwrite' => true,
            ],
            'X-Content-Type-Options' => [
                'value'     => 'nosniff',
                'overwrite' => true,
            ],
            'Referrer-Policy'        => [
                'value'     => 'no-referrer',
                'overwrite' => true,
            ],
        ],

        /**
         * Route-specific headers
         */
        'security::generate-token' => [
            'Cache-Control' => [
                'value'     => 'no-store',
                'overwrite' => true,
            ],
            'Pragma'        => [
                'value'     => 'no-cache',
                'overwrite' => true,
            ],
        ],
        'security::refresh-token'  => [
            'Cache-Control' => [
                'value'     => 'no-store',
                'overwrite' => true,
            ],
            'Pragma'        => [
                'value'     => 'no-cache',
                'overwrite' => true,
            ],
        ],
    ],
];
