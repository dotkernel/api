<?php
/**
 * Local configuration.
 *
 * Copy this file to `local.php` and change its settings as required.
 * `local.php` is ignored by git and safe to use for local and sensitive data like usernames and passwords.
 */

declare(strict_types=1);

use AppTest\Helper\TestHelper;

if (! TestHelper::isTestMode()) {
    return [];
}

$databases = [
    'default' => [
        'url' => 'sqlite:///:memory:',
    ],
];

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => $databases['default']
            ],
        ],
    ],
    'test_mode' => true,
    'authentication' => [
        'pdo' => [
            'dsn' => 'sqlite::memory:',
//            'dsn' => sprintf('mysql:host=%s;port=%d;dbname=%s',
//                'test',
//                3306,
//                'test'
//            ),
//            'username' => 'test',
//            'password' => 'test',
        ],
    ]
];
