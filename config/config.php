<?php

declare(strict_types=1);

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.

$cacheConfig = [
    'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new Laminas\ConfigAggregator\ConfigAggregator([
    Mezzio\Authorization\ConfigProvider::class,
    Mezzio\Authorization\Acl\ConfigProvider::class,
    Mezzio\Authorization\Rbac\ConfigProvider::class,
    Mezzio\Authentication\ConfigProvider::class,
    Mezzio\Authentication\OAuth2\ConfigProvider::class,
    Mezzio\Cors\ConfigProvider::class,
    Mezzio\Hal\ConfigProvider::class,
    Mezzio\ProblemDetails\ConfigProvider::class,
    Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    Mezzio\Twig\ConfigProvider::class,
    Laminas\Diactoros\ConfigProvider::class,
    Laminas\InputFilter\ConfigProvider::class,
    Laminas\Filter\ConfigProvider::class,
    Laminas\HttpHandlerRunner\ConfigProvider::class,
    Laminas\Hydrator\ConfigProvider::class,
    Laminas\Validator\ConfigProvider::class,
    // Include cache configuration
    new Laminas\ConfigAggregator\ArrayProvider($cacheConfig),
    Mezzio\Helper\ConfigProvider::class,
    Mezzio\ConfigProvider::class,
    Mezzio\Router\ConfigProvider::class,
    class_exists(Mezzio\Tooling\ConfigProvider::class)
        ? Mezzio\Tooling\ConfigProvider::class
        : function () {
            return [];
        },

    // DK packages
    Dot\Cli\ConfigProvider::class,
    Dot\Log\ConfigProvider::class,
    Dot\ErrorHandler\ConfigProvider::class,
    Dot\DependencyInjection\ConfigProvider::class,
    Dot\ResponseHeader\ConfigProvider::class,
    Dot\Mail\ConfigProvider::class,
    Dot\DataFixtures\ConfigProvider::class,
    Dot\Cache\ConfigProvider::class,

    // Default App module config
    Api\Admin\ConfigProvider::class,
    Api\App\ConfigProvider::class,
    Api\User\ConfigProvider::class,
    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new Laminas\ConfigAggregator\PhpFileProvider(
        realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local,{,*.}test}.php'
    ),
    // Load development config if it exists
    new Laminas\ConfigAggregator\PhpFileProvider(
        realpath(__DIR__) . '/development.config.php'
    ),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
