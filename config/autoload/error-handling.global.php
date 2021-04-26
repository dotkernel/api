<?php

use Laminas\Log\Logger;
use Laminas\Log\Formatter\Json;

return [
    'dot-errorhandler' => [
        'loggerEnabled' => true,
        'logger' => 'dot-log.default_logger'
    ],
    'dot_log' => [
        'loggers' => [
            'default_logger' => [
                'writers' => [
                    'FileWriter' => [
                        'name' => 'stream',
                        'priority' => Logger::ALERT,
                        'options' => [
                            'stream' => __DIR__ . '/../../log/error-log-{Y}-{m}-{d}.log',
                            // explicitly log all messages
                            'filters' => [
                                'allMessages' => [
                                    'name' => 'priority',
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
    'error-report' => [
        'filePath' => sprintf('%s/../../log/error-report-endpoint-log.log', __DIR__)
    ]
];
