<?php

declare(strict_types=1);

use Fig\Http\Message\StatusCodeInterface;

return [
    /**
     * Unless specified when creating an error response,
     * mezzio-problem-details will set type based on the status code specified in the exception
     */
    'problem-details' => [
        'default_types_map' => [
            StatusCodeInterface::STATUS_BAD_REQUEST
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-400-bad-request',
            StatusCodeInterface::STATUS_UNAUTHORIZED
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-401-unauthorized',
            StatusCodeInterface::STATUS_FORBIDDEN
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-403-forbidden',
            StatusCodeInterface::STATUS_NOT_FOUND
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-404-not-found',
            StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-405-method-not-allowed',
            StatusCodeInterface::STATUS_NOT_ACCEPTABLE
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-406-not-acceptable',
            StatusCodeInterface::STATUS_CONFLICT
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-409-conflict',
            StatusCodeInterface::STATUS_GONE
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-410-gone',
            StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-415-unsupported-media-type',
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
                => 'https://datatracker.ietf.org/doc/html/rfc9110#name-500-internal-server-error',
        ],
    ],
];
