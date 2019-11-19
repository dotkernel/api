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
$config = $container->get('config');
$loadFromCache = $config['documentation']['loadFromCache'] ?? false;
$showMarker = $config['documentation']['showMarker'] ?? false;
$cacheFile = $config['documentation']['cacheFile'] ?? null;

switch ($_GET['action'] ?? 'display') {
    case 'generate':
        if (!$loadFromCache || !file_exists($cacheFile)) {
            $aggregator = new ConfigAggregator([
                new ZendConfigProvider('./public/documentation/json/*.{json}'),
                new ZendConfigProvider('./public/documentation/json/*/*.{json}'),
                // you can tell the aggregator to look for more nested folders by adding:
                // new ZendConfigProvider('./public/documentation/json/*/*/*.{json}'),
            ]);
            $documentation = $aggregator->getMergedConfig();
            $documentation['servers'] = $config['documentation']['servers'] ?? [];
            $documentation['info']['title'] = $config['application']['name'] . ' Documentation';
            $documentation['info']['description'] = $config['application']['name'] . ' Documentation';

            $writer = new PhpArray();
            $writer->toFile($cacheFile, $documentation);
        } else {
            $documentation = require $cacheFile;
        }
        ksort($documentation['components']['schemas']);

        header('Content-Type: application/json');
        exit(json_encode($documentation));
        break;

    case 'display':
        $baseUrl = $config['application']['url'];
        $documentationUrl = $baseUrl . '/documentation';
        $cacheInfo['class'] = $loadFromCache ? ' cached' : ' live';
        $cacheInfo['visibility'] = $showMarker ? '' : ' hidden';
        echo <<<MARKUP
<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>{$config['application']['name']} - Documentation</title>
        <link rel="icon" type="image/png" href="{$baseUrl}/favicon.ico" />
        <link rel="stylesheet" type="text/css" href="{$documentationUrl}/css/swagger-ui.css" />
        <link rel="stylesheet" type="text/css" href="{$documentationUrl}/css/styles.css" />
    </head>
    <body>
        <div class="cacheInfo{$cacheInfo['class']}{$cacheInfo['visibility']}"></div>
        <div id="swagger-ui"></div>
        <script src="{$documentationUrl}/js/swagger-ui-bundle.js"></script>
        <script src="{$documentationUrl}/js/swagger-ui-standalone-preset.js"></script>
        <script>
            window.onload = function() {
                // Begin Swagger UI call region
                window.ui = SwaggerUIBundle({
                    url: "{$documentationUrl}/index.php?action=generate",
                    dom_id: '#swagger-ui',
                    deepLinking: true,
                    presets: [
                        SwaggerUIBundle.presets.apis,
                        SwaggerUIStandalonePreset
                    ],
                    plugins: [
                        SwaggerUIBundle.plugins.DownloadUrl
                    ],
                    layout: "StandaloneLayout",
                    supportedSubmitMethods: ['delete', 'get', 'patch', 'post', 'put']
                });
                // End Swagger UI call region
            }
        </script>
    </body>
</html>
MARKUP;
        break;
}
