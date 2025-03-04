<?php

declare(strict_types=1);

namespace Api\User;

use Api\User\Entity\User;
use Api\User\Entity\UserAvatar;
use Api\User\Entity\UserDetail;
use Api\User\Entity\UserResetPassword;
use Api\User\Entity\UserRole;
use Api\User\Enum\UserResetPasswordStatusEnum;
use Api\User\Enum\UserStatusEnum;
use Api\User\Handler\AccountActivateHandler;
use Api\User\Handler\AccountAvatarHandler;
use Api\User\Handler\AccountHandler;
use Api\User\Handler\AccountRecoveryHandler;
use Api\User\Handler\AccountResetPasswordHandler;
use Api\User\Handler\UserActivateHandler;
use Api\User\Handler\UserAvatarHandler;
use Api\User\Handler\UserHandler;
use Api\User\Handler\UserRoleHandler;
use DateTimeImmutable;
use Fig\Http\Message\StatusCodeInterface;
use OpenApi\Attributes as OA;

/**
 * @see UserHandler::delete()
 */
#[OA\Delete(
    path: '/user/{uuid}',
    description: 'Authenticated (super)admin deletes (anonymizes) a user account identified by its UUID',
    summary: 'Admin deletes (anonymizes) user account',
    security: [['AuthToken' => []]],
    tags: ['User'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'User UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_NO_CONTENT,
            description: 'User account has been deleted (anonymized)',
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see UserHandler::get()
 */
#[OA\Get(
    path: '/user/{uuid}',
    description: 'Authenticated (super)admin fetches a user account identified by its UUID',
    summary: 'Admin views user account',
    security: [['AuthToken' => []]],
    tags: ['User'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'User UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'User account',
            content: new OA\JsonContent(ref: '#/components/schemas/User'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see UserHandler::getCollection()
 */
#[OA\Get(
    path: '/user',
    description: 'Authenticated (super)admin fetches a list of user accounts',
    summary: 'Admin lists user accounts',
    security: [['AuthToken' => []]],
    tags: ['User'],
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
                new OA\Examples(example: 'user.identity', summary: 'Identity', value: 'user.identity'),
                new OA\Examples(example: 'user.status', summary: 'Status', value: 'user.status'),
                new OA\Examples(example: 'user.created', summary: 'Created', value: 'user.created'),
                new OA\Examples(example: 'user.updated', summary: 'Updated', value: 'user.updated'),
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
            description: 'List of user accounts',
            content: new OA\JsonContent(ref: '#/components/schemas/UserCollection'),
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
 * @see UserHandler::patch()
 */
#[OA\Patch(
    path: '/user/{uuid}',
    description: 'Authenticated (super)admin updates an existing user account',
    summary: 'Admin updates user account',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Update user account request',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'identity', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'passwordConfirm', type: 'string'),
                new OA\Property(property: 'status', type: 'string', default: UserStatusEnum::Active),
                new OA\Property(
                    property: 'detail',
                    properties: [
                        new OA\Property(property: 'firstName', type: 'string'),
                        new OA\Property(property: 'lastName', type: 'string'),
                        new OA\Property(property: 'email', type: 'string'),
                    ],
                    type: 'object',
                ),
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
    tags: ['User'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'User UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'User account updated',
            content: new OA\JsonContent(ref: '#/components/schemas/User'),
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
 * @see UserHandler::post()
 */
#[OA\Post(
    path: '/user',
    description: 'Authenticated (super)admin creates a new user account',
    summary: 'Admin creates user account',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Create user account request',
        required: true,
        content: new OA\JsonContent(
            required: ['identity', 'password', 'passwordConfirm', 'roles'],
            properties: [
                new OA\Property(property: 'identity', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'passwordConfirm', type: 'string'),
                new OA\Property(property: 'status', type: 'string', default: UserStatusEnum::Active),
                new OA\Property(
                    property: 'detail',
                    required: ['email'],
                    properties: [
                        new OA\Property(property: 'firstName', type: 'string'),
                        new OA\Property(property: 'lastName', type: 'string'),
                        new OA\Property(property: 'email', type: 'string'),
                    ],
                    type: 'object',
                ),
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
    tags: ['User'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: 'User account created',
            content: new OA\JsonContent(ref: '#/components/schemas/User'),
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
        new OA\Response(
            response: StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            description: 'Mail error',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see UserActivateHandler::patch()
 */
#[OA\Patch(
    path: '/user/{uuid}/activate',
    description: 'Authenticated (super)admin activates an existing user account',
    summary: 'Admin activates user account',
    security: [['AuthToken' => []]],
    tags: ['User'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'User UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'User account activated',
            content: new OA\JsonContent(ref: '#/components/schemas/InfoMessage'),
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
        new OA\Response(
            response: StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            description: 'Mail error',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see UserAvatarHandler::delete()
 */
#[OA\Delete(
    path: '/user/{uuid}/avatar',
    description: 'Authenticated (super)admin deletes a user avatar identified by user UUID',
    summary: 'Admin deletes user avatar',
    security: [['AuthToken' => []]],
    tags: ['UserAvatar'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'User UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_NO_CONTENT,
            description: 'User avatar has been deleted',
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see UserAvatarHandler::get()
 */
#[OA\Get(
    path: '/user/{uuid}/avatar',
    description: 'Authenticated (super)admin fetches a user avatar identified by user UUID',
    summary: 'Admin views user avatar',
    security: [['AuthToken' => []]],
    tags: ['UserAvatar'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'User UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'User avatar',
            content: new OA\JsonContent(ref: '#/components/schemas/UserAvatar'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see UserAvatarHandler::post()
 */
#[OA\Post(
    path: '/user/{uuid}/avatar',
    description: 'Authenticated (super)admin creates user avatar for user identified by user UUID',
    summary: 'Admin creates user avatar',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Create user avatar request',
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['avatar'],
                properties: [
                    new OA\Property(property: 'avatar', type: 'file', format: 'binary'),
                ],
                type: 'object',
            ),
        ),
    ),
    tags: ['UserAvatar'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'User UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: 'User avatar created',
            content: new OA\JsonContent(ref: '#/components/schemas/UserAvatar'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
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
 * @see UserRoleHandler::get()
 */
#[OA\Get(
    path: '/user/role/{uuid}',
    description: 'Authenticated (super)admin fetches a user role identified by its UUID',
    summary: 'Admin views user role',
    security: [['AuthToken' => []]],
    tags: ['UserRole'],
    parameters: [
        new OA\Parameter(
            name: 'uuid',
            description: 'UserRole UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'User role',
            content: new OA\JsonContent(ref: '#/components/schemas/UserRole'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see UserRoleHandler::getCollection()
 */
#[OA\Get(
    path: '/user/role',
    description: 'Authenticated (super)admin fetches a list of user roles',
    summary: 'Admin lists user roles',
    security: [['AuthToken' => []]],
    tags: ['UserRole'],
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
            description: 'List of user roles',
            content: new OA\JsonContent(ref: '#/components/schemas/UserRoleCollection'),
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
 * @see AccountHandler::delete()
 */
#[OA\Delete(
    path: '/user/my-account',
    description: 'Authenticated user deletes (anonymizes) their own account',
    summary: 'User deletes (anonymizes) their own account',
    security: [['AuthToken' => []]],
    tags: ['User'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_NO_CONTENT,
            description: 'User account has been deleted (anonymized)',
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            description: 'Error',
        ),
    ],
)]

/**
 * @see AccountHandler::get()
 */
#[OA\Get(
    path: '/user/my-account',
    description: 'Authenticated user fetches their own account data',
    summary: 'User fetches their own account',
    security: [['AuthToken' => []]],
    tags: ['User'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'My user account',
            content: new OA\JsonContent(ref: '#/components/schemas/User'),
        ),
    ],
)]

/**
 * @see AccountHandler::patch()
 */
#[OA\Patch(
    path: '/user/my-account',
    description: 'Authenticated user updates their own account data',
    summary: 'User updates their own account',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Update my user account request',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'passwordConfirm', type: 'string'),
                new OA\Property(
                    property: 'detail',
                    properties: [
                        new OA\Property(property: 'firstName', type: 'string'),
                        new OA\Property(property: 'lastName', type: 'string'),
                        new OA\Property(property: 'email', type: 'string'),
                    ],
                    type: 'object',
                ),
            ],
            type: 'object',
        ),
    ),
    tags: ['User'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'My admin account',
            content: new OA\JsonContent(ref: '#/components/schemas/User'),
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
 * @see AccountHandler::post()
 */
#[OA\Post(
    path: '/account/register',
    description: 'Register user account',
    summary: 'Unauthenticated user registers new user account',
    requestBody: new OA\RequestBody(
        description: 'Create user account request',
        required: true,
        content: new OA\JsonContent(
            required: ['identity', 'password', 'passwordConfirm', 'detail'],
            properties: [
                new OA\Property(property: 'identity', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'passwordConfirm', type: 'string'),
                new OA\Property(
                    property: 'detail',
                    required: ['email'],
                    properties: [
                        new OA\Property(property: 'firstName', type: 'string'),
                        new OA\Property(property: 'lastName', type: 'string'),
                        new OA\Property(property: 'email', type: 'string'),
                    ],
                    type: 'object',
                ),
            ],
            type: 'object',
        ),
    ),
    tags: ['User'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: 'User account created',
            content: new OA\JsonContent(ref: '#/components/schemas/User'),
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
        new OA\Response(
            response: StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            description: 'Mail error',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see AccountAvatarHandler::delete()
 */
#[OA\Delete(
    path: '/user/my-avatar',
    description: 'Authenticated user deletes their user avatar',
    summary: 'User deletes their own avatar',
    security: [['AuthToken' => []]],
    tags: ['UserAvatar'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_NO_CONTENT,
            description: 'User avatar has been deleted',
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see AccountAvatarHandler::get()
 */
#[OA\Get(
    path: '/user/my-avatar',
    description: 'Authenticated user fetches their own avatar',
    summary: 'User fetches their own avatar',
    security: [['AuthToken' => []]],
    tags: ['UserAvatar'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'User avatar',
            content: new OA\JsonContent(ref: '#/components/schemas/UserAvatar'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see AccountAvatarHandler::post()
 */
#[OA\Post(
    path: '/user/my-avatar',
    description: 'Authenticated user creates their own avatar',
    summary: 'User creates their own avatar',
    security: [['AuthToken' => []]],
    requestBody: new OA\RequestBody(
        description: 'Create user avatar request',
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['avatar'],
                properties: [
                    new OA\Property(property: 'avatar', type: 'file', format: 'binary'),
                ],
                type: 'object',
            ),
        ),
    ),
    tags: ['UserAvatar'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: 'User avatar created',
            content: new OA\JsonContent(ref: '#/components/schemas/UserAvatar'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_BAD_REQUEST,
            description: 'Bad Request',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see AccountResetPasswordHandler::get()
 */
#[OA\Get(
    path: '/account/reset-password/{hash}',
    description: 'Unauthenticated user fetches a reset password by its hash',
    summary: 'Unauthenticated user fetches reset password',
    tags: ['ResetPassword'],
    parameters: [
        new OA\Parameter(
            name: 'hash',
            description: 'Reset password hash',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'Reset password status',
            content: new OA\JsonContent(ref: '#/components/schemas/InfoMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_GONE,
            description: 'Gone (expired)',
        ),
    ],
)]

/**
 * @see AccountResetPasswordHandler::patch()
 */
#[OA\Patch(
    path: '/account/reset-password/{hash}',
    description: 'Unauthenticated user modifies their password using a reset password identified by its hash',
    summary: 'Unauthenticated user modifies their password',
    requestBody: new OA\RequestBody(
        description: 'Modify password request',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'passwordConfirm', type: 'string'),
            ],
            type: 'object',
        ),
    ),
    tags: ['ResetPassword'],
    parameters: [
        new OA\Parameter(
            name: 'hash',
            description: 'Reset password hash',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'Reset password status',
            content: new OA\JsonContent(ref: '#/components/schemas/InfoMessage'),
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
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_GONE,
            description: 'Gone (expired)',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            description: 'Mail error',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see AccountResetPasswordHandler::post()
 */
#[OA\Post(
    path: '/account/reset-password',
    description: 'Unauthenticated user requests to reset their password by providing their email/identity',
    summary: 'Unauthenticated user requests to modify their password',
    requestBody: new OA\RequestBody(
        description: 'Reset password request',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            oneOf: [
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'email', type: 'string'),
                    ],
                ),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'identity', type: 'string'),
                    ],
                ),
            ],
        ),
    ),
    tags: ['ResetPassword'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: 'Reset password created',
            content: new OA\JsonContent(ref: '#/components/schemas/InfoMessage'),
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
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            description: 'Mail error',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see AccountRecoveryHandler::post()
 */
#[OA\Post(
    path: '/account/recover-identity',
    description: 'Unauthenticated user recovers their identity by providing their email',
    summary: 'Unauthenticated user recovers their identity',
    requestBody: new OA\RequestBody(
        description: 'Recover identity request',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string'),
            ],
            type: 'object',
        ),
    ),
    tags: ['RecoverIdentity'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: 'Identity sent via email',
            content: new OA\JsonContent(ref: '#/components/schemas/InfoMessage'),
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
        new OA\Response(
            response: StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            description: 'Mail error',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see AccountActivateHandler::patch()
 */
#[OA\Patch(
    path: '/account/activate/{hash}',
    description: 'Unauthenticated user activates their account using the hash from an activation link',
    summary: 'Unauthenticated user activates their account',
    tags: ['ActivateUser'],
    parameters: [
        new OA\Parameter(
            name: 'hash',
            description: 'User activation hash',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string'),
        ),
    ],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_OK,
            description: 'Account activated',
            content: new OA\JsonContent(ref: '#/components/schemas/InfoMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_CONFLICT,
            description: 'Conflict',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_NOT_FOUND,
            description: 'Not Found',
        ),
    ],
)]

/**
 * @see AccountActivateHandler::post()
 */
#[OA\Post(
    path: '/account/activate',
    description: 'Unauthenticated user requests an account activation link by providing their email',
    summary: 'Unauthenticated user requests to activate account',
    requestBody: new OA\RequestBody(
        description: 'Account activation request',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'email', type: 'string'),
            ],
            type: 'object',
        ),
    ),
    tags: ['ActivateUser'],
    responses: [
        new OA\Response(
            response: StatusCodeInterface::STATUS_CREATED,
            description: 'Account activation requested',
            content: new OA\JsonContent(ref: '#/components/schemas/InfoMessage'),
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
        ),
        new OA\Response(
            response: StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            description: 'Mail error',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorMessage'),
        ),
    ],
)]

/**
 * @see User
 */
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: '1234abcd-abcd-4321-12ab-123456abcdef'),
        new OA\Property(property: 'hash', type: 'string'),
        new OA\Property(property: 'identity', type: 'string'),
        new OA\Property(property: 'status', type: 'string', example: UserStatusEnum::Active),
        new OA\Property(property: 'avatar', ref: '#/components/schemas/UserAvatar', nullable: true),
        new OA\Property(property: 'detail', ref: '#/components/schemas/UserDetail'),
        new OA\Property(
            property: 'roles',
            type: 'array',
            items: new OA\Items(
                ref: '#/components/schemas/UserRole',
            ),
        ),
        new OA\Property(
            property: 'resetPasswords',
            type: 'array',
            items: new OA\Items(
                ref: '#/components/schemas/UserResetPassword',
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
                            example: 'https://example.com/user/1234abcd-abcd-4321-12ab-123456abcdef',
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
 * @see UserAvatar
 */
#[OA\Schema(
    schema: 'UserAvatar',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: '1234abcd-abcd-4321-12ab-123456abcdef'),
        new OA\Property(
            property: 'url',
            type: 'string',
            example: 'https://example.com/uploads/user/1234abcd-abcd-4321-12ab-123456abcdef/'
            . 'avatar-1234abcd-abcd-4321-12ab-123456abcdef.jpg',
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
                            example: 'https://example.com/user/1234abcd-abcd-4321-12ab-123456abcdef/avatar',
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
 * @see UserDetail
 */
#[OA\Schema(
    schema: 'UserDetail',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: '1234abcd-abcd-4321-12ab-123456abcdef'),
        new OA\Property(property: 'firstName', type: 'string'),
        new OA\Property(property: 'lastName', type: 'string'),
        new OA\Property(property: 'email', type: 'string'),
        new OA\Property(property: 'created', type: 'object', example: new DateTimeImmutable()),
        new OA\Property(property: 'updated', type: 'object', example: new DateTimeImmutable()),
    ],
    type: 'object',
)]

/**
 * @see UserResetPassword
 */
#[OA\Schema(
    schema: 'UserResetPassword',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: '1234abcd-abcd-4321-12ab-123456abcdef'),
        new OA\Property(property: 'expires', type: 'object', example: new DateTimeImmutable()),
        new OA\Property(property: 'hash', type: 'string'),
        new OA\Property(property: 'status', type: 'string', example: UserResetPasswordStatusEnum::Requested),
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
                            example: 'https://example.com/user/1234abcd-abcd-4321-12ab-123456abcdef',
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
 * @see UserRole
 */
#[OA\Schema(
    schema: 'UserRole',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', example: '1234abcd-abcd-4321-12ab-123456abcdef'),
        new OA\Property(property: 'name', type: 'string', example: UserRole::ROLE_USER),
        new OA\Property(
            property: '_links',
            properties: [
                new OA\Property(
                    property: 'self',
                    properties: [
                        new OA\Property(
                            property: 'href',
                            type: 'string',
                            example: 'https://example.com/user/role/1234abcd-abcd-4321-12ab-123456abcdef',
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

#[OA\Schema(
    schema: 'UserCollection',
    properties: [
        new OA\Property(
            property: '_embedded',
            properties: [
                new OA\Property(
                    property: 'users',
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/User',
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

#[OA\Schema(
    schema: 'UserRoleCollection',
    properties: [
        new OA\Property(
            property: '_embedded',
            properties: [
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/UserRole',
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
