<?php

/**
 * OpenAPI document root, consumed by bin/generate-openapi.php.
 *
 * These values cannot live in the OA attributes on Api\App\OpenAPI: attribute arguments must be
 * constant expressions, so they cannot read the base URL out of the local autoload config. The
 * server URL is taken from the `application.url` key defined there, and its label from the
 * optional `openapi.server_description` key — both are per-environment, so both belong in the
 * local config. Everything below is environment-independent and belongs in this file.
 */

declare(strict_types=1);

return [
    'openapi' => [
        /**
         * Where bin/generate-openapi.php writes the document.
         *
         * `public/` is what makes the document reachable over HTTP; move it outside if the spec
         * should not be served, e.g. `'output_file' => 'data/openapi.yaml'`.
         */
        'output_file' => 'public/openapi.yaml',
        /**
         * Tags whose endpoints are left out of the generated document.
         *
         * Use this to stop publishing a module's endpoints: name the tag its operations carry and both
         * the paths and the schemas only they referenced will disappear from the generated document.
         * The endpoints keep working — this hides them from the document, it is not access control.
         *
         * Match the tag exactly as the operations declare it, including spaces; the name is quoted
         * before it reaches the filter, so no escaping is needed here. Names listed below are also
         * dropped from the `tags` block, since an excluded tag has no operations left to describe.
         *
         * Example — keep the simulator and the card on-ramp out of the public document:
         *
         *     'exclude_tags' => [
         *         'Simulator',
         *         'Funding',
         *     ],
         *
         * Regenerate with `composer openapi` afterward; the run reports the remaining path count.
         */
        'exclude_tags' => [],
        /**
         * Servers published after the project's own URL.
         *
         * The first server in the generated document is always `application.url`, labelled with
         * `openapi.server_description`. The entries below follow it in this order, each with a `url` and an
         * optional `description`. A URL already in the list — the project's own included — is written once,
         * and the first occurrence keeps its description.
         *
         * Example — also publish a sandbox:
         *
         *     'servers' => [
         *         ['url' => 'https://sandbox.example.com', 'description' => 'Sandbox'],
         *     ],
         *
         * An entry without a non-empty `url` fails `composer openapi`.
         */
        'servers' => [],
        /**
         * Timezone for the `info.x-generated` build timestamp, as an IANA identifier.
         *
         * OpenAPI has no field for when a document was built, so it is written as a specification
         * extension on the info object — the part of the document that describes the document rather
         * than the API. The value is ISO-8601 with the offset included, e.g.
         * `2026-09-08T10:39:29-04:00`, so it stays unambiguous across daylight saving.
         *
         * Set it to null to leave the field out. That also makes the output byte-identical between
         * runs, which is worth having if the document is committed — a timestamp changes on every
         * generation and shows up as a diff even when no endpoint did.
         */
        'generated_timezone' => 'UTC',
        'openapi_version'    => '3.1.0',
        'info'               => [
            'title'   => 'Dotkernel API',
            'version' => '1.0',
        ],
        'external_docs'      => [
            'description' => 'Dotkernel API documentation',
            'url'         => 'https://docs.dotkernel.org/api-documentation/',
        ],
        'tags'               => [
            'AccessToken'     => 'OAuth2 token issue and refresh.',
            'ActivateUser'    => 'Administrative activation and deactivation of users.',
            'Admin'           => 'Administrator records and the administrator\'s own account.',
            'AdminRole'       => 'Administrator role catalogue.',
            'ErrorReport'     => 'Error reporting for third-party clients.',
            'Home'            => 'Application root.',
            'RecoverIdentity' => 'Recovery of a forgotten sign-in identity.',
            'ResetPassword'   => 'Password reset request and completion.',
            'User'            => 'User records and the caller\'s own account.',
            'UserAvatar'      => 'User and account avatars.',
            'UserRole'        => 'User role catalogue.',
        ],
        'server_description' => 'Local development server',
        'security_schemes'   => [
            'AuthToken'           => [
                'type'          => 'http',
                'bearer_format' => 'JWT',
                'scheme'        => 'bearer',
            ],
            'ErrorReportingToken' => [
                'type' => 'apiKey',
                'name' => 'Error-Reporting-Token',
                'in'   => 'header',
            ],
        ],
    ],
];
