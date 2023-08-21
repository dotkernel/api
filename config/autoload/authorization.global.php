<?php

declare(strict_types=1);

use Api\Admin\Entity\AdminRole;
use Api\User\Entity\UserRole;

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
     * C has B as a parent. That means B inherits the permissions of C, and A inherits the permissions of C.
     */
    'mezzio-authorization-rbac' => [
        'roles'       => [
            AdminRole::ROLE_SUPERUSER => [],
            AdminRole::ROLE_ADMIN     => [
                AdminRole::ROLE_SUPERUSER,
            ],
            UserRole::ROLE_GUEST      => [
                UserRole::ROLE_USER,
            ],
        ],
        'permissions' => [
            AdminRole::ROLE_SUPERUSER => [],
            AdminRole::ROLE_ADMIN     => [
                'admin.my-account.update',
                'admin.my-account.view',
                'admin.create',
                'admin.delete',
                'admin.list',
                'admin.update',
                'admin.view',
                'admin.role.list',
                'admin.role.view',
                'user.activate',
                'user.create',
                'user.list',
                'user.delete',
                'user.view',
                'user.update',
                'user.avatar.create',
                'user.avatar.delete',
                'user.avatar.view',
                'user.role.list',
                'user.role.view',
                'error.report',
                'home',
            ],
            UserRole::ROLE_USER       => [
                'user.my-account.delete',
                'user.my-account.update',
                'user.my-account.view',
                'user.my-avatar.create',
                'user.my-avatar.delete',
                'user.my-avatar.view',
            ],
            UserRole::ROLE_GUEST      => [
                'account.activate.request',
                'account.activate',
                'account.register',
                'account.modify-password',
                'account.recover-identity',
                'account.reset-password.validate',
                'account.reset-password.request',
                'security.generate-token',
                'security.refresh-token',
                'error.report',
                'home',
                'user.create',
            ],
        ],
    ],
];
