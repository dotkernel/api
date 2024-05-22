<?php

return [
    'content-negotiation' => [
        'default'  => [ // default to any route if not configured above
            'Accept'       => [
                'application/json',
                'application/hal+json',
            ],
            'Content-Type' => [
                'application/json',
                'application/hal+json',
            ]
        ],
        'your.route.name' => [
            'Accept'       => [],
            'Content-Type' => []
        ]
    ]
];
