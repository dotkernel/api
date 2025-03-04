<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Handler\ErrorReportHandler;
use Api\App\Handler\HomeHandler;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\Authentication\OAuth2\TokenEndpointHandler;
use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0', title: 'DotKernel API')]
#[OA\Server(url: 'http://api.dotkernel.localhost', description: 'Local development server')]
#[OA\SecurityScheme(securityScheme: 'AuthToken', type: 'http', in: 'header', bearerFormat: 'JWT', scheme: 'bearer')]
#[OA\SecurityScheme(securityScheme: 'ErrorReportingToken', type: 'apiKey', in: 'header', name: 'Error-Reporting-Token')]

#[OA\ExternalDocumentation(
    description: 'Dotkernel API documentation',
    url: 'https://docs.dotkernel.org/api-documentation/'
)]

/**
 * @see HomeHandler::get()
 */
#[OA\Get(
    path: '/',
    description: 'API home page outputting default message',
    summary: 'API home page',
    tags: ['Home'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'OK',
            content: new OA\JsonContent(
                ref: '#/components/schemas/HomeMessage',
                title: 'HomeMessage',
                description: 'API home page output message',
            ),
        ),
    ],
)]

/**
 * @see ErrorReportHandler::post()
 */
#[OA\Post(
    path: '/error-report',
    description: 'Third-party application reports an error to the API',
    summary: 'Report an error to the API',
    security: [['ErrorReportingToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Error reporting request',
        required: true,
        content: new OA\JsonContent(
            required: ['message'],
            properties: [
                new OA\Property(property: 'message', type: 'string'),
            ],
            type: 'object',
        )
    ),
    tags: ['ErrorReport'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: 'Created',
            content: new OA\JsonContent(ref: '#/components/schemas/InfoMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_UNAUTHORIZED,
            description: 'Unauthorized',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_FORBIDDEN,
            description: 'Forbidden',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            description: 'Error',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see TokenEndpointHandler::handle()
 */
#[OA\Post(
    path: '/security/generate-token',
    description: 'Client generates access token using username and password',
    summary: 'Generate access token',
    requestBody: new OA\RequestBody(
        description: 'Access token generation request',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'grant_type', type: 'string', default: 'password'),
                new OA\Property(property: 'client_id', type: 'string', enum: ['admin', 'frontend']),
                new OA\Property(property: 'client_secret', type: 'string', enum: ['admin', 'frontend']),
                new OA\Property(property: 'scope', type: 'string', default: 'api'),
                new OA\Property(property: 'username', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
            ],
            type: 'object',
        ),
    ),
    tags: ['AccessToken'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'OK',
            content: new OA\JsonContent(ref: '#/components/schemas/OAuth2SuccessMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
            content: new OA\JsonContent(ref: '#/components/schemas/OAuth2GenerateErrorMessage'),
        ),
    ],
)]

/**
 * @see TokenEndpointHandler::handle()
 */
#[OA\Post(
    path: '/security/refresh-token',
    description: 'Client refreshes access token using refresh token',
    summary: 'Refresh access token',
    requestBody: new OA\RequestBody(
        description: 'Access token refresh request',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'grant_type', type: 'string', default: 'refresh_token'),
                new OA\Property(property: 'client_id', type: 'string', enum: ['admin', 'frontend']),
                new OA\Property(property: 'client_secret', type: 'string', enum: ['admin', 'frontend']),
                new OA\Property(property: 'scope', type: 'string', default: 'api'),
                new OA\Property(property: 'refresh_token', type: 'string'),
            ],
            type: 'object',
        ),
    ),
    tags: ['AccessToken'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'OK',
            content: new OA\JsonContent(ref: '#/components/schemas/OAuth2SuccessMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_UNAUTHORIZED,
            description: 'Unauthorized',
            content: new OA\JsonContent(ref: '#/components/schemas/OAuth2RefreshErrorMessage'),
        ),
    ],
)]

#[OA\Schema(
    schema: 'OAuth2GenerateErrorMessage',
    properties: [
        new OA\Property(property: 'error', type: 'string'),
        new OA\Property(property: 'error_description', type: 'string'),
        new OA\Property(property: 'message', type: 'string'),
    ],
    type: 'object',
)]

#[OA\Schema(
    schema: 'OAuth2RefreshErrorMessage',
    properties: [
        new OA\Property(property: 'hint', type: 'string'),
    ],
    type: 'object',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/OAuth2GenerateErrorMessage'),
    ],
)]

#[OA\Schema(
    schema: 'OAuth2SuccessMessage',
    properties: [
        new OA\Property(property: 'token_type', type: 'string', default: 'Bearer'),
        new OA\Property(property: 'expires_in', type: 'integer', default: 86400),
        new OA\Property(property: 'access_token', type: 'string'),
        new OA\Property(property: 'refresh_token', type: 'string'),
    ],
    type: 'object',
)]

#[OA\Schema(
    schema: 'HomeMessage',
    properties: [
        new OA\Property(property: 'message', type: 'string', default: 'DotKernel API version 5'),
    ],
    type: 'object'
)]

#[OA\Schema(
    schema: 'ErrorMessage',
    properties: [
        new OA\Property(
            property: 'error',
            properties: [
                new OA\Property(property: 'messages', type: 'array', items: new OA\Items(type: 'string')),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]

#[OA\Schema(
    schema: 'InfoMessage',
    properties: [
        new OA\Property(
            property: 'info',
            properties: [
                new OA\Property(property: 'messages', type: 'array', items: new OA\Items(type: 'string')),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]

#[OA\Schema(
    schema: 'Collection',
    description: 'Base collection providing common structure to be extended by entity-specific collections',
    properties: [
        new OA\Property(property: '_total_items', type: 'integer', example: 1),
        new OA\Property(property: '_page', type: 'integer', example: 1),
        new OA\Property(property: '_page_count', type: 'integer', example: 1),
        new OA\Property(
            property: '_links',
            required: ['self'],
            properties: [
                new OA\Property(
                    property: 'first',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            type: 'string',
                            example: 'https://example.com/resource?page=1',
                        ),
                    ],
                    type: 'object',
                ),
                new OA\Property(
                    property: 'prev',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            type: 'string',
                            example: 'https://example.com/resource?page=2',
                        ),
                    ],
                    type: 'object',
                ),
                new OA\Property(
                    property: 'self',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            type: 'string',
                            example: 'https://example.com/resource?page=3',
                        ),
                    ],
                    type: 'object',
                ),
                new OA\Property(
                    property: 'next',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            type: 'string',
                            example: 'https://example.com/resource?page=4',
                        ),
                    ],
                    type: 'object',
                ),
                new OA\Property(
                    property: 'last',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            type: 'string',
                            example: 'https://example.com/resource?page=5',
                        ),
                    ],
                    type: 'object',
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]

class OpenAPI
{
}
