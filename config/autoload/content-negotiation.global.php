<?php

declare(strict_types=1);

return [
    'content-negotiation' => [
        'default'               => [ // default to any route if not configured above
            'Accept'       => [ // the Accept is what format the server can send back
                'application/json',
                'application/hal+json',
            ],
            'Content-Type' => [  // the Content-Type is what format the server can process
                'application/json',
                'application/hal+json',
            ],
        ],
        'your.route.name'       => [
            'Accept'       => [],
            'Content-Type' => [],
        ],
        'user.avatar.create'    => [
            'Accept'       => [
                'application/json',
                'application/hal+json',
            ],
            'Content-Type' => [
                'multipart/form-data',
            ],
        ],
        'user.my-avatar.create' => [
            'Accept'       => [
                'application/json',
                'application/hal+json',
            ],
            'Content-Type' => 'multipart/form-data',
        ],
    ],
];
