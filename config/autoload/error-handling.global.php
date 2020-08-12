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
                            'stream' => sprintf('%s/../../log/error-log-%s.log',
                                __DIR__,
                                date('Y-m-d')
                            ),
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
