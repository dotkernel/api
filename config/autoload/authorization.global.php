<?php

declare(strict_types=1);

use Api\User\Entity\UserRole;
use Api\User\Entity\AdminRole;

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
    'mezzio-authorization-rbac' => [
        'roles' => [
            AdminRole::ROLE_SUPERUSER => [],
            AdminRole::ROLE_ADMIN => [AdminRole::ROLE_SUPERUSER],
            UserRole::ROLE_USER => [AdminRole::ROLE_ADMIN],
            UserRole::ROLE_GUEST => [UserRole::ROLE_USER]
        ],
        'permissions' => [
            AdminRole::ROLE_SUPERUSER => [],
            AdminRole::ROLE_ADMIN => [
                'user:activate',
                'user:avatar',
                'user:list,create',
                'user:delete,view,update',
            ],
            UserRole::ROLE_USER => [
                'my-account:avatar',
                'my-account:me',
            ],
            UserRole::ROLE_GUEST => []
        ],
    ]
];
