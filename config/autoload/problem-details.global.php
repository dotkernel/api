<?php

declare(strict_types=1);

use Api\App\Service\ErrorReportServiceInterface;
use Fig\Http\Message\StatusCodeInterface;

return [
    /**
     * Unless specified when throwing the exception,
     * mezzio-problem-details will set type based on the status code specified in the exception
     */
    'problem-details' => [
        'default_types_map' => [
            StatusCodeInterface::STATUS_BAD_REQUEST            => 'https://example.com/error/bad-request',
            StatusCodeInterface::STATUS_UNAUTHORIZED           => 'https://example.com/error/unauthorized',
            StatusCodeInterface::STATUS_FORBIDDEN              => 'https://example.com/error/forbidden',
            StatusCodeInterface::STATUS_NOT_FOUND              => 'https://example.com/error/not-found',
            StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED     => 'https://example.com/error/method-not-allowed',
            StatusCodeInterface::STATUS_NOT_ACCEPTABLE         => 'https://example.com/error/method-not-acceptable',
            StatusCodeInterface::STATUS_CONFLICT               => 'https://example.com/error/conflict',
            StatusCodeInterface::STATUS_GONE                   => 'https://example.com/error/gone',
            StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE => 'https://example.com/error/unsupported-media-type',
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR  => 'https://example.com/error/internal-server-error',
        ],
    ],

    /**
     * Misc documentation URLs used application-wide
     */
    'application' => [
        'versioning' => [
            'documentation_url' => 'https://docs.dotkernel.org/api-documentation/v5/core-features/versioning',
        ],
    ],

    /**
     * Error-reporting specific documentation URLs
     */
    ErrorReportServiceInterface::class => [
        'documentation_url' => 'https://example.com/error/not-authorized/error-reporting',
    ],
];
