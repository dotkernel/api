<?php

declare(strict_types=1);

const ENVIRONMENT_DEVELOPMENT = 'development';
const ENVIRONMENT_PRODUCTION  = 'production';

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

function copyFile(array $file): void
{
    if (is_readable($file['destination'])) {
        echo "File {$file['destination']} already exists." . PHP_EOL;
    } else {
        if (! in_array(getEnvironment(), $file['environment'])) {
            echo "Skipping the copy of {$file['source']} due to environment settings." . PHP_EOL;
        } else {
            if (! copy($file['source'], $file['destination'])) {
                echo "Cannot copy {$file['source']} file to {$file['destination']}" . PHP_EOL;
            } else {
                echo "File {$file['source']} copied successfully to {$file['destination']}." . PHP_EOL;
            }
        }
    }
}

function getEnvironment(): string
{
    $composerInstalledJson = 'vendor/composer/installed.json';
    if (! file_exists($composerInstalledJson)) {
        throw new RuntimeException('Composer installed JSON file not found.');
    }

    $data = json_decode(file_get_contents($composerInstalledJson), true);
    foreach ($data as $package) {
        if (! empty($package['dev'])) {
            return ENVIRONMENT_DEVELOPMENT;
        }
    }

    return ENVIRONMENT_PRODUCTION;
}

// when adding files to the below array the `source` and `destination` paths must be relative to the project root folder
// the `environment` key will indicate on what environments the file will be copied,
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
