<?php

declare(strict_types=1);

namespace Api\Admin;

use Api\Admin\Collection\AdminCollection;
use Api\Admin\Collection\AdminRoleCollection;
use Api\Admin\Entity\Admin;
use Api\Admin\Entity\AdminRole;
use Api\Admin\Enum\AdminStatusEnum;
use Api\Admin\Handler\AdminAccountHandler;
use Api\Admin\Handler\AdminHandler;
use Api\Admin\Handler\AdminRoleHandler;
use DateTimeImmutable;
use Fig\Http\Message\StatusCodeInterface;
use OpenApi\Attributes as OA;

/**
 * @see AdminAccountHandler::get()
 */
#[OA\Get(
    path: '/admin/my-account',
    description: 'Authenticated (super)admin fetches their own account data',
    summary: 'Admin fetches their own account',
    security: [['AuthToken' => []]],
    tags: ['Admin'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'My admin account',
            content: new OA\JsonContent(ref: '#/components/schemas/Admin'),
        ),
    ],
)]

/**
 * @see AdminAccountHandler::patch()
 */
#[OA\Patch(
    path: '/admin/my-account',
    description: 'Authenticated (super)admin updates their own account data',
    summary: 'Admin updates their own account',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Update my admin account request',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'passwordConfirm', type: 'string'),
                new OA\Property(property: 'firstName', type: 'string'),
                new OA\Property(property: 'lastName', type: 'string'),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(
                        required: ['uuid'],
                        properties: [
                            new OA\Property(property: 'uuid', type: 'string'),
                        ],
                    ),
                ),
            ],
            type: 'object',
        ),
    ),
    tags: ['Admin'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'My admin account',
            content: new OA\JsonContent(ref: '#/components/schemas/Admin'),
        ),
    ],
)]

/**
 * @see AdminHandler::delete()
 */
#[OA\Delete(
    path: '/admin/{uuid}',
    description: 'Authenticated (super)admin deletes an admin account identified by its UUID',
    summary: 'Admin deletes an admin account',
    security: [['AuthToken' => []]],
    tags: ['Admin'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'Admin UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_NO_CONTENT,
            description: 'Admin account has been deleted',
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see AdminHandler::get()
 */
#[OA\Get(
    path: '/admin/{uuid}',
    description: 'Authenticated (super)admin fetches an admin account identified by its UUID',
    summary: 'Admin fetches an admin account',
    security: [['AuthToken' => []]],
    tags: ['Admin'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'Admin UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'Admin account',
            content: new OA\JsonContent(ref: '#/components/schemas/Admin'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see AdminHandler::getCollection()
 */
#[OA\Get(
    path: '/admin',
    description: 'Authenticated (super)admin fetches a list of admin accounts',
    summary: 'Admin lists admin accounts',
    security: [['AuthToken' => []]],
    tags: ['Admin'],
    parameters: [
        new OA\Parameter(
            name: 'page',
            description: 'Page number',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer'),
            example: 1,
        ),
        new OA\Parameter(
            name: 'limit',
            description: 'Limit',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer'),
            example: 10,
        ),
        new OA\Parameter(
            name: 'order',
            description: 'Sort by field',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string'),
            examples: [
                new OA\Examples(example: 'admin.identity', summary: 'Identity', value: 'admin.identity'),
                new OA\Examples(example: 'admin.firstName', summary: 'Firstname', value: 'admin.firstName'),
                new OA\Examples(example: 'admin.lastName', summary: 'Lastname', value: 'admin.lastName'),
                new OA\Examples(example: 'admin.status', summary: 'Status', value: 'admin.status'),
                new OA\Examples(example: 'admin.created', summary: 'Created', value: 'admin.created'),
                new OA\Examples(example: 'admin.updated', summary: 'Updated', value: 'admin.updated'),
            ],
        ),
        new OA\Parameter(
            name: 'dir',
            description: 'Sort direction',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string'),
            examples: [
                new OA\Examples(example: 'desc', summary: 'Sort descending', value: 'desc'),
                new OA\Examples(example: 'asc', summary: 'Sort ascending', value: 'asc'),
            ],
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'List of admin accounts',
            content: new OA\JsonContent(ref: '#/components/schemas/AdminCollection'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see AdminHandler::patch()
 */
#[OA\Patch(
    path: '/admin/{uuid}',
    description: 'Authenticated (super)admin updates an existing admin account',
    summary: 'Admin updates an admin account',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Update admin account request',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'passwordConfirm', type: 'string'),
                new OA\Property(property: 'firstName', type: 'string'),
                new OA\Property(property: 'lastName', type: 'string'),
                new OA\Property(property: 'status', type: 'string', default: AdminStatusEnum::Active),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(
                        required: ['uuid'],
                        properties: [
                            new OA\Property(property: 'uuid', type: 'string'),
                        ],
                    ),
                ),
            ],
            type: 'object',
        ),
    ),
    tags: ['Admin'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'Admin UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'Admin account updated',
            content: new OA\JsonContent(ref: '#/components/schemas/Admin'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_CONFLICT,
            description: 'Conflict',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see AdminHandler::post()
 */
#[OA\Post(
    path: '/admin',
    description: 'Authenticated (super)admin creates a new admin account',
    summary: 'Admin creates an admin account',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Create admin account request',
        required: true,
        content: new OA\JsonContent(
            required: ['identity', 'password', 'passwordConfirm', 'firstName', 'lastName', 'roles'],
            properties: [
                new OA\Property(property: 'identity', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'passwordConfirm', type: 'string'),
                new OA\Property(property: 'firstName', type: 'string'),
                new OA\Property(property: 'lastName', type: 'string'),
                new OA\Property(property: 'status', type: 'string', default: AdminStatusEnum::Active),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(
                        required: ['uuid'],
                        properties: [
                            new OA\Property(property: 'uuid', type: 'string'),
                        ],
                    ),
                ),
            ],
            type: 'object',
        ),
    ),
    tags: ['Admin'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: 'Admin account created',
            content: new OA\JsonContent(ref: '#/components/schemas/Admin'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_CONFLICT,
            description: 'Conflict',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see AdminRoleHandler::get()
 */
#[OA\Get(
    path: '/admin/role/{uuid}',
    description: 'Authenticated (super)admin fetches an admin role identified by its UUID',
    summary: 'Admin fetches an admin role',
    security: [['AuthToken' => []]],
    tags: ['AdminRole'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'Admin role UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'Admin role',
            content: new OA\JsonContent(ref: '#/components/schemas/AdminRole'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see AdminRoleHandler::getCollection()
 */
#[OA\Get(
    path: '/admin/role',
    description: 'Authenticated (super)admin fetches a list of admin roles',
    summary: 'Admin lists admin roles',
    security: [['AuthToken' => []]],
    tags: ['AdminRole'],
    parameters: [
        new OA\Parameter(
            name: 'page',
            description: 'Page number',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer'),
            example: 1,
        ),
        new OA\Parameter(
            name: 'limit',
            description: 'Limit',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer'),
            example: 10,
        ),
        new OA\Parameter(
            name: 'order',
            description: 'Sort by field',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string'),
            examples: [
                new OA\Examples(example: 'role.name', summary: 'Name', value: 'role.name'),
                new OA\Examples(example: 'role.created', summary: 'Created', value: 'role.created'),
                new OA\Examples(example: 'role.updated', summary: 'Updated', value: 'role.updated'),
            ],
        ),
        new OA\Parameter(
            name: 'dir',
            description: 'Sort direction',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string'),
            examples: [
                new OA\Examples(example: 'desc', summary: 'Sort descending', value: 'desc'),
                new OA\Examples(example: 'asc', summary: 'Sort ascending', value: 'asc'),
            ],
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'List of admin accounts',
            content: new OA\JsonContent(ref: '#/components/schemas/AdminRoleCollection'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see Admin
 */
#[OA\Schema(
    schema: 'Admin',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: '1234abcd-abcd-4321-12ab-123456abcdef'),
        new OA\Property(property: 'identity', type: 'string'),
        new OA\Property(property: 'firstName', type: 'string'),
        new OA\Property(property: 'lastName', type: 'string'),
        new OA\Property(property: 'status', type: 'string', example: AdminStatusEnum::Active),
        new OA\Property(
            property: 'roles',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'uuid', type: 'string'),
                    new OA\Property(property: 'name', type: 'string', example: AdminRole::ROLE_ADMIN),
                ],
                type: 'object',
            ),
        ),
        new OA\Property(property: 'created', type: 'object', example: new DateTimeImmutable()),
        new OA\Property(property: 'updated', type: 'object', example: new DateTimeImmutable()),
        new OA\Property(
            property: '_links',
            properties: [
                new OA\Property(
                    property: 'self',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            type: 'string',
                            example: 'https://example.com/admin/1234abcd-abcd-4321-12ab-123456abcdef',
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

/**
 * @see AdminRole
 */
#[OA\Schema(
    schema: 'AdminRole',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: '1234abcd-abcd-4321-12ab-123456abcdef'),
        new OA\Property(property: 'name', type: 'string', example: AdminRole::ROLE_ADMIN),
        new OA\Property(
            property: '_links',
            properties: [
                new OA\Property(
                    property: 'self',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            type: 'string',
                            example: 'https://example.com/admin/role/1234abcd-abcd-4321-12ab-123456abcdef',
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

/**
 * @see AdminCollection
 */
#[OA\Schema(
    schema: 'AdminCollection',
    properties: [
        new OA\Property(
            property: '_embedded',
            properties: [
                new OA\Property(
                    property: 'admins',
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/Admin',
                    ),
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/Collection'),
    ],
)]
/**
 * @see AdminRoleCollection
 */
#[OA\Schema(
    schema: 'AdminRoleCollection',
    properties: [
        new OA\Property(
            property: '_embedded',
            properties: [
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/AdminRole',
                    ),
                ),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/Collection'),
    ],
)]

class OpenAPI
{
}
