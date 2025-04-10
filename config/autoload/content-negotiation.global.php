<?php

declare(strict_types=1);

return [
    'content-negotiation' => [
        'default'                     => [ // default to any route if not configured above
            'Accept'       => [ // the Accept is what format the server can send back
                'application/json',
                'application/hal+json',
            ],
            'Content-Type' => [  // the Content-Type is what format the server can process
                'application/json',
                'application/hal+json',
            ],
        ],
        'your.route.name'             => [
            'Accept'       => [],
            'Content-Type' => [],
        ],
        'user::create-account-avatar' => [
            'Accept'       => [
                'application/json',
                'application/hal+json',
            ],
            'Content-Type' => [
                'multipart/form-data',
            ],
        ],
        'user::create-user-avatar'    => [
            'Accept'       => [
                'application/json',
                'application/hal+json',
            ],
            'Content-Type' => 'multipart/form-data',
        ],
    ],
];
