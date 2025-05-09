<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

const ENVIRONMENT_DEVELOPMENT = 'development';
const ENVIRONMENT_PRODUCTION  = 'production';

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * @param array{source: string, destination: string, environment: array<string>} $file
 */
function copyFile(array $file): void
{
    if (! in_array(getEnvironment(), $file['environment'])) {
        echo "Skipping the copy of {$file['source']} due to environment settings." . PHP_EOL;
        return;
    }

    if (is_readable($file['destination'])) {
        echo "File {$file['destination']} already exists. Skipping..." . PHP_EOL;
        return;
    }

    if (! copy($file['source'], $file['destination'])) {
        echo "Cannot copy {$file['source']} file to {$file['destination']}" . PHP_EOL;
    } else {
        echo "File {$file['source']} copied successfully to {$file['destination']}." . PHP_EOL;
    }
}

function getEnvironment(): string
{
    return getenv('COMPOSER_DEV_MODE') === '1' ? ENVIRONMENT_DEVELOPMENT : ENVIRONMENT_PRODUCTION;
}

/**
 * When adding files to the below array:
 * - `source` and `destination` paths must be relative to the project root folder
 * - `environment` key will indicate on what environments the file will be copied
 */
$files = [
    [
        'source'      => 'config/autoload/local.php.dist',
        'destination' => 'config/autoload/local.php',
        'environment' => [ENVIRONMENT_DEVELOPMENT, ENVIRONMENT_PRODUCTION],
    ],
    [
        'source'      => 'config/autoload/local.test.php.dist',
        'destination' => 'config/autoload/local.test.php',
        'environment' => [ENVIRONMENT_DEVELOPMENT],
    ],
    [
        'source'      => 'vendor/dotkernel/dot-mail/config/mail.global.php.dist',
        'destination' => 'config/autoload/mail.global.php',
        'environment' => [ENVIRONMENT_DEVELOPMENT, ENVIRONMENT_PRODUCTION],
    ],
];

echo "Using environment setting: " . getEnvironment() . PHP_EOL;

array_walk($files, 'copyFile');
