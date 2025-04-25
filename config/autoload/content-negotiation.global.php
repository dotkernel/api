<?php

declare(strict_types=1);

use Api\App\Middleware\ContentNegotiationMiddleware;

return [
    'content-negotiation' => [
        // default to any route if not configured below
        ContentNegotiationMiddleware::DEFAULT_HEADERS => [
            // the Accept is what format the server can send back
            'Accept' => [
                'application/json',
                'application/hal+json',
            ],
            // the Content-Type is what format the server can process
            'Content-Type' => [
                'application/json',
                'application/hal+json',
            ],
        ],
        'user::create-account-avatar'                 => [
            'Accept'       => [
                'application/json',
                'application/hal+json',
            ],
            'Content-Type' => [
                'multipart/form-data',
            ],
        ],
        'user::create-user-avatar'                    => [
            'Accept'       => [
                'application/json',
                'application/hal+json',
            ],
            'Content-Type' => 'multipart/form-data',
        ],
    ],
];
