<?php

$host = $_SERVER['HTTP_HOST'];

echo <<<MARKUP
<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>DotKernel API</title>
        <link rel="stylesheet" type="text/css" href="//{$host}/documentation/swagger-ui.css" >
        <link rel="icon" type="image/png" href="//{$host}/favicon.ico" sizes="16x16" />
        <style type="text/css">
            html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
            *, *:before, *:after {box-sizing: inherit; }
            body { margin:0; background: #fafafa; }
        </style>
    </head>
    <body>
        <div id="swagger-ui"></div>
        <script src="//{$host}/documentation/swagger-ui-bundle.js"></script>
        <script src="//{$host}/documentation/swagger-ui-standalone-preset.js"></script>
        <script>
            window.onload = function() {
                // Begin Swagger UI call region
                window.ui = SwaggerUIBundle({
                    url: "//{$host}/documentation/api.yaml",
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
                    supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
                });
                // End Swagger UI call region
            }
        </script>
    </body>
</html>
MARKUP;
