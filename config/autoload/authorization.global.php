<?php

declare(strict_types=1);

use Api\User\Entity\UserRoleEntity;

return [
    /**
     * Example:
        'roles' => [
            'A' => [],
            'B' => ['A'],
            'C' => ['B'],
        ],
     * A has no parent role.
     * B has A as a parent. That means A inherits the permissions of B.
     * C has B as a parent. That means C inherits the permissions of B, and A inherits the permissions of C.
     */
    'zend-expressive-authorization-rbac' => [
        'roles' => [
            UserRoleEntity::ROLE_ADMIN  => [],
            UserRoleEntity::ROLE_MEMBER => [UserRoleEntity::ROLE_ADMIN]
        ],
        'permissions' => [
            UserRoleEntity::ROLE_ADMIN => [
                'user:activate',
                'user:avatar',
                'user:list,create',
                'user:delete,view,update',
                'user:subscription',
            ],
            UserRoleEntity::ROLE_MEMBER => [
                'my-account:avatar',
                'my-account:me',
                'my-account:subscription',
            ],
        ],
    ]
];
