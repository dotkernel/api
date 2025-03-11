<?php

declare(strict_types=1);

use Core\Admin\Entity\AdminRole;
use Core\User\Entity\UserRole;

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
                'admin::list-admin',
                'admin::create-admin',
                'admin::delete-admin',
                'admin::view-admin',
                'admin::update-admin',
                'admin::list-role',
                'admin::view-role',
                'admin::view-account',
                'admin::update-account',
                'user::list-user',
                'user::create-user',
                'user::delete-user',
                'user::view-user',
                'user::update-user',
                'user::delete-user-avatar',
                'user::view-user-avatar',
                'user::create-user-avatar',
                'user::list-role',
                'user::view-role',
                'user::activate-user',
                'user::deactivate-user',
                'app::create-error-report',
                'app::view-index',
            ],
            UserRole::ROLE_USER       => [
                'user::delete-account',
                'user::view-account',
                'user::update-account',
                'user::delete-account-avatar',
                'user::view-account-avatar',
                'user::create-account-avatar',
            ],
            UserRole::ROLE_GUEST      => [
                'app::create-error-report',
                'app::view-index',
                'user::activate-account',
                'user::request-activate-account',
                'user::recover-account',
                'user::check-account-reset-password',
                'user::update-account-reset-password',
                'user::create-account-reset-password',
                'user::create-account',
                'security::token',
                'security::token',
            ],
        ],
    ],
];
