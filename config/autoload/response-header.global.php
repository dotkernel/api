<?php

declare(strict_types=1);

return [
    'dot_response_headers' => [
        /**
         * Global headers - applied to all routes
         */
        '*' => [
            'permissions-policy' => [
                // Federated Learning of Cohorts
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
