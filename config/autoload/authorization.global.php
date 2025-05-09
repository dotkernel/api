<?php

declare(strict_types=1);

use Core\Admin\Enum\AdminRoleEnum;
use Core\User\Enum\UserRoleEnum;

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
            AdminRoleEnum::Superuser->value => [],
            AdminRoleEnum::Admin->value     => [
                AdminRoleEnum::Superuser->value,
            ],
            UserRoleEnum::Guest->value      => [
                UserRoleEnum::User->value,
            ],
        ],
        'permissions' => [
            AdminRoleEnum::Superuser->value => [],
            AdminRoleEnum::Admin->value     => [
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
            UserRoleEnum::User->value       => [
                'user::delete-account',
                'user::view-account',
                'user::update-account',
                'user::delete-account-avatar',
                'user::view-account-avatar',
                'user::create-account-avatar',
            ],
            UserRoleEnum::Guest->value      => [
                'app::create-error-report',
                'app::view-index',
                'user::activate-account',
                'user::request-activate-account',
                'user::recover-account',
                'user::check-account-reset-password',
                'user::update-account-reset-password',
                'user::create-account-reset-password',
                'user::create-account',
                'security::generate-token',
                'security::refresh-token',
            ],
        ],
    ],
];
