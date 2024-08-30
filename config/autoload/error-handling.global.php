<?php

declare(strict_types=1);

use Api\App\Service\ErrorReportServiceInterface;
use Dot\Log\Formatter\Json;
use Dot\Log\Logger;

return [
    'dot-errorhandler' => [
        'loggerEnabled' => true,
        'logger'        => 'dot-log.default_logger',
    ],
    'dot_log'          => [
        'loggers' => [
            'default_logger' => [
                'writers' => [
                    'FileWriter' => [
                        'name'     => 'stream',
                        'priority' => Logger::ALERT,
                        'options'  => [
                            'stream'    => __DIR__ . '/../../log/error-log-{Y}-{m}-{d}.log',
                            'filters'   => [
                                'allMessages' => [
                                    'name'    => 'priority',
                                    'options' => [
                                        'operator' => '>=',
                                        'priority' => Logger::EMERG,
                                    ],
                                ],
                            ],
                            'formatter' => [
                                'name' => Json::class,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    /**
     * Messages will be stored only if all the below conditions are met:
     * - enabled = true
     * - request headers contain a valid error reporting token
     * - at least one of the domain_whitelist OR ip_whitelist checks passes
     */
    ErrorReportServiceInterface::class => [
        /**
         * Usage:
         * If enabled is set to true, further checks are performed and if all good, message is stored.
         * If enabled is set to false, no message is stored and an error message is returned.
         */
        'enabled' => true,

        /**
         * Path to the file where messages will be stored.
         * If it does not exist, it will be created.
         */
        'path' => __DIR__ . '/../../log/error-report-endpoint-log.log',

        /**
         * In order to store messages, requests sent to the error reporting endpoint, must contain a header having:
         * - name: the value of \Api\App\Service\ErrorReportService::HEADER_NAME
         * - value: one of the items in this array
         */
        'tokens' => [],

        /**
         * Usage:
         * 1. Missing/empty domain_whitelist => no domain is allowed to store messages.
         * 2. Add '*' to allow any domain to store messages.
         * 3. If you want to whitelist only specific domains, add them to domain_whitelist.
         */
        'domain_whitelist' => [],

        /**
         * Usage:
         * 1. Missing/empty ip_whitelist => no IP address is allowed to store messages.
         * 2. Add '*' to allow any IP address to store messages.
         * 3. If you want to whitelist only specific IP addresses, add them to ip_whitelist.
         */
        'ip_whitelist' => [],
    ],
];
