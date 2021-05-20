<?php

declare(strict_types=1);

return [
    'dot_response_headers' => [
        /**
         * Global headers - applied to all routes
         */
        '*' => [
            'permissions-policy' => [
                'value' => 'interest-cohort=()',
                'overwrite' => true,
            ],
        ],

        /**
         * Route-specific headers
         */
//        'route-name' => [
//            'header-name' => [
//                'value' => 'header-value',
//                'overwrite' => true,
//            ]
//        ],
    ]
];
