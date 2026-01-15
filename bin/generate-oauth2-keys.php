<?php

declare(strict_types=1);

$files = [
    './data/oauth/encryption.key',
    './data/oauth/private.key',
    './data/oauth/public.key',
];

foreach ($files as $file) {
    if (! file_exists($file)) {
        return include './vendor/mezzio/mezzio-authentication-oauth2/bin/generate-oauth2-keys';
    }
}

echo 'OAuth2 keys already exist. Skipping...' . PHP_EOL;
return 0;
