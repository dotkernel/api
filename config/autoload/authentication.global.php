<?php

declare(strict_types=1);

return [
    'authentication' => [
        'private_key'    => [
            'key_or_path'           => realpath(__DIR__ . '/../../data/oauth/private.key'),
            'key_permissions_check' => false,
        ],
        'public_key'     => [
            'key_or_path'           => realpath(__DIR__ . '/../../data/oauth/public.key'),
            'key_permissions_check' => false,
        ],
        'encryption_key' => require realpath(__DIR__ . '/../../data/oauth/encryption.key'),

        //Period interval formatting:
        //P period (used with D days, W weeks, M months, Y years) or PT (used with H hours, M minutes, S seconds)
        //a number precedes each unit, e.g. P2D, PT10M

        //Usage examples:
        //P1M is 1 calendar month - for exactly 30 days use P30D
        //P2DT3H is 2 days + 3 hours
        //PT3600S is 3600 seconds
        'access_token_expire'  => 'P1D', //1 day
        'refresh_token_expire' => 'P1M', //1 calendar month
        'auth_code_expire'     => 'PT10M', //10 minutes
        'invalid_credentials'  => [
            'error'             => 'Invalid credentials.',
            'error_description' => 'Invalid credentials.',
            'message'           => 'Invalid credentials.',
        ],
    ],
];
