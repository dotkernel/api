<?php

declare(strict_types=1);

namespace Api\App;

use Api\App\Handler\GetIndexResourceHandler;
use Api\App\Handler\PostErrorReportResourceHandler;
use Fig\Http\Message\StatusCodeInterface;
use OpenApi\Attributes as OA;

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
        new OA\Property(property: 'message', type: 'string', default: 'Dotkernel API version 7'),
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
    schema: 'DateTimeObject',
    title: 'DateTimeObject',
    description: 'A timestamp as this API puts it on the wire. Entities hand their DateTimeImmutable '
    . 'straight to the serializer, so a timestamp arrives as PHP\'s own object form rather than as an '
    . 'ISO-8601 string. `date` carries microsecond precision and no offset; the zone is named '
    . 'separately in `timezone`.',
    properties: [
        new OA\Property(
            property: 'date',
            description: 'Local date and time in the named zone, to microseconds',
            type: 'string',
            example: '2026-09-08 11:15:09.421498',
        ),
        new OA\Property(
            property: 'timezone_type',
            description: 'How `timezone` is expressed: 1 offset, 2 abbreviation, 3 identifier',
            type: 'integer',
            example: 3,
            enum: [1, 2, 3],
        ),
        new OA\Property(property: 'timezone', type: 'string', example: 'UTC'),
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
