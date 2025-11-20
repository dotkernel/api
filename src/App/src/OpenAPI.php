<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Handler\GetIndexResourceHandler;
use Api\App\Handler\PostErrorReportResourceHandler;
use Fig\Http\Message\StatusCodeInterface;
use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0', title: 'Dotkernel API')]
#[OA\Server(url: 'http://api.dotkernel.localhost', description: 'Local development server')]
#[OA\SecurityScheme(securityScheme: 'AuthToken', type: 'http', in: 'header', bearerFormat: 'JWT', scheme: 'bearer')]
#[OA\SecurityScheme(securityScheme: 'ErrorReportingToken', type: 'apiKey', name: 'Error-Reporting-Token', in: 'header')]

#[OA\ExternalDocumentation(
    description: 'Dotkernel API documentation',
    url: 'https://docs.dotkernel.org/api-documentation/'
)]

/**
 * @see GetIndexResourceHandler::handle()
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
 * @see PostErrorReportResourceHandler::handle()
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

#[OA\Schema(
    schema: 'HomeMessage',
    properties: [
        new OA\Property(property: 'message', type: 'string', default: 'Dotkernel API version 6'),
    ],
    type: 'object',
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
