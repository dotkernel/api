<?php

use Api\App\Common\Message;
use Api\Console\User\Handler\ListUsersHandler;
use Api\User\Entity\UserEntity;
use Zend\Filter\Callback;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Validator\Digits;
use Zend\Validator\InArray;

/**
 * Documentation: https://github.com/zfcampus/zf-console
 */
return [
    'dot_console' => [
        'name' => 'DotKernel API Console',
        'commands' => [
            [
                'name' => 'list-users',
                'route' => '[--page=] [--search=] [--status=] [--deleted=]',
                'description' => 'List all users based on a set of optional filters.',
                'short_description' => 'List users.',
                'options_descriptions' => [
                    'page' => '(Optional) Page number',
                    'search' => '(Optional) Filter users by search string.',
                    'status' => '(Optional) Filter users by status. (' . implode(', ', UserEntity::STATUSES) . ')',
                    'deleted' => '(Optional) Filter users by deletion status (true, false)'
                ],
                'defaults' => ['page' => 1, 'search' => null, 'status' => null, 'deleted' => null],
                'filters' => [
                    'page' => new Callback(function ($value) {
                        $validator = new Digits();
                        if ($validator->isValid($value)) {
                            return (int)$value;
                        }
                        throw new Exception(sprintf(Message::INVALID_VALUE, 'page'));
                    }),
                    'search' => new Callback(function ($value) {
                        $value = (new StringTrim())->filter($value);
                        $value = (new StripTags())->filter($value);
                        return $value;
                    }),
                    'status' => new Callback(function ($value) {
                        $validator = new InArray();
                        $validator->setHaystack(UserEntity::STATUSES);
                        if ($validator->isValid($value)) {
                            return $value;
                        }
                        throw new Exception(sprintf(Message::INVALID_VALUE, 'status'));
                    }),
                    'deleted' => new Callback(function ($value) {
                        $validator = new InArray();
                        $validator->setHaystack(['true', 'false']);
                        if ($validator->isValid($value)) {
                            return $value;
                        }
                        throw new Exception(sprintf(Message::INVALID_VALUE, 'deleted'));
                    })
                ],
                'handler' => ListUsersHandler::class
            ]
        ]
    ]
];
