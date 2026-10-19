<?php

declare(strict_types=1);

use Api\App\Service\ErrorReportServiceInterface;
use Dot\ErrorHandler\Extra\ExtraProvider;
use Dot\ErrorHandler\Extra\Processor\RequestProcessor;
use Dot\ErrorHandler\Extra\Processor\TraceProcessor;
use Dot\ErrorHandler\Extra\Provider\RequestProvider;
use Dot\ErrorHandler\Extra\Provider\TraceProvider;
use Dot\ErrorHandler\Extra\ReplacementStrategy;
use Dot\Log\Formatter\Json;
use Dot\Log\Logger;

return [
    'dot-errorhandler' => [
        'loggerEnabled'           => true,
        'logger'                  => 'dot-log.default_logger',
        ExtraProvider::CONFIG_KEY => [
            TraceProvider::class => [
                'enabled'   => true,
                'processor' => [
                    'class' => TraceProcessor::class,
                ],
            ],
            /**
             * Logs the parsed request body. Keys containing any of the sensitiveParameters are masked,
             * but other personal data (emails, names etc.) is logged as-is.
             */
            RequestProvider::class => [
                'enabled'   => false,
                'processor' => [
                    'class'               => RequestProcessor::class,
                    'replacementStrategy' => ReplacementStrategy::Full,
                    'sensitiveParameters' => [
                        'password',
                        'token',
                        'secret',
                    ],
                ],
            ],
        ],
    ],
    'dot_log'          => [
        'loggers' => [
            'default_logger' => [
                'writers' => [
                    'FileWriter' => [
                        'name'    => 'stream',
                        'level'   => Logger::ALERT,
                        'options' => [
                            'stream'    => __DIR__ . '/../../log/error-log-{Y}-{m}-{d}.log',
                            'filters'   => [
                                'allMessages' => [
                                    'name'    => 'level',
                                    'options' => [
                                        'operator' => '>=',
                                        'level'    => Logger::EMERG,
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

        /**
         * Documentation URL for problem details response
         */
        'documentation_url' => 'https://docs.dotkernel.org/api-documentation/v5/core-features/error-reporting/',
    ],
];
