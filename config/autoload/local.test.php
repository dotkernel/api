<?php
/**
 * Local test configuration.
 *
 * Overwrites the database connection to use an in memory database
 */

declare(strict_types=1);

use AppTest\Helper\TestHelper;

if (! TestHelper::isTestMode()) {
    return [];
}

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'url' => 'sqlite:///:memory:',
                ]
            ],
        ],
    ],
];
