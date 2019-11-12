<?php

declare(strict_types=1);

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
    'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new Zend\ConfigAggregator\ConfigAggregator([
    Zend\Expressive\Authorization\Acl\ConfigProvider::class,
    Zend\Expressive\Authorization\Rbac\ConfigProvider::class,
    Zend\Expressive\Authentication\ConfigProvider::class,
    Zend\Expressive\Authentication\OAuth2\ConfigProvider::class,
    Zend\InputFilter\ConfigProvider::class,
    Zend\Filter\ConfigProvider::class,
    Zend\Validator\ConfigProvider::class,
    Zend\Hydrator\ConfigProvider::class,
    Zend\Paginator\ConfigProvider::class,
    Zend\Expressive\Hal\ConfigProvider::class,
    Zend\ProblemDetails\ConfigProvider::class,
    Zend\Db\ConfigProvider::class,
    Zend\Expressive\Router\FastRouteRouter\ConfigProvider::class,
    Zend\HttpHandlerRunner\ConfigProvider::class,
    // Include cache configuration
    new Zend\ConfigAggregator\ArrayProvider($cacheConfig),
    Zend\Expressive\Helper\ConfigProvider::class,
    Zend\Expressive\ConfigProvider::class,
    Zend\Expressive\Router\ConfigProvider::class,
    // DK packages
    Dot\Console\ConfigProvider::class,
    Dot\Log\ConfigProvider::class,
    Dot\ErrorHandler\ConfigProvider::class,
    Dot\AnnotatedServices\ConfigProvider::class,
    // Default App module config
    Api\App\ConfigProvider::class,
    Api\App\Doctrine\ConfigProvider::class,
    Api\Console\ConfigProvider::class,
    Api\Device\ConfigProvider::class,
    Api\User\ConfigProvider::class,
    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new Zend\ConfigAggregator\PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),
    // Load development config if it exists
    new Zend\ConfigAggregator\PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
