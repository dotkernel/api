<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Zend\Config\Writer\PhpArray;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\ZendConfigProvider;

chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require 'config/container.php';
$config = $container->get('config')['documentation'] ?? [];

switch ($_GET['action'] ?? 'display') {
    case 'generate':
        if (empty($config['cacheConfig']) || !file_exists($config['cacheTarget'])) {
            $aggregator = new ConfigAggregator([
                new ZendConfigProvider('public/documentation/json/*.{json}'),
                new ZendConfigProvider('public/documentation/json/*/*.{json}'),
                // you can tell the aggregator to look for more nested folders by adding:
                // new ZendConfigProvider('public/documentation/json/*/*/*.{json}'),
            ]);
            $documentation = $aggregator->getMergedConfig();

            $writer = new PhpArray();
            $writer->toFile($config['cacheTarget'], $documentation);
        } else {
            $documentation = require $config['cacheTarget'];
        }
        ksort($documentation['components']['schemas']);

        header('Content-Type: application/json');
        exit(json_encode($documentation));
        break;

    case 'display':
        $cacheInfo['class'] = $config['cacheConfig'] ? 'cached' : 'live';
        $cacheInfo['visibility'] = $config['showMarker'] ? '' : 'hidden';
        echo <<<MARKUP
<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>DotKernel API</title>
        <link rel="stylesheet" type="text/css" href="//{$_SERVER['HTTP_HOST']}/documentation/swagger-ui.css" />
        <link rel="stylesheet" type="text/css" href="//{$_SERVER['HTTP_HOST']}/documentation/styles.css" />
        <link rel="icon" type="image/png" href="//{$_SERVER['HTTP_HOST']}/favicon.ico" sizes="16x16" />
        <script src="//{$_SERVER['HTTP_HOST']}/documentation/swagger-ui-bundle.js"></script>
        <script src="//{$_SERVER['HTTP_HOST']}/documentation/swagger-ui-standalone-preset.js"></script>
    </head>
    <body>
        <div class="cacheInfo {$cacheInfo['class']} {$cacheInfo['visibility']}"></div>
        <div id="swagger-ui"></div>
        <script>
            window.onload = function() {
                // Begin Swagger UI call region
                window.ui = SwaggerUIBundle({
                    url: "//{$_SERVER['HTTP_HOST']}/documentation/index.php?action=generate",
                    dom_id: '#swagger-ui',
                    deepLinking: true,
                    presets: [
                        SwaggerUIBundle.presets.apis,
                        SwaggerUIStandalonePreset
                    ],
                    plugins: [
                        SwaggerUIBundle.plugins.DownloadUrl
                    ],
                    // layout: "StandaloneLayout",
                    supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch']
                });
                // End Swagger UI call region
            }
        </script>
    </body>
</html>
MARKUP;
        break;
}
