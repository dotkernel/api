<?php

declare(strict_types=1);

namespace App\User\Form;

use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\Identical;
use Zend\Validator\StringLength;

/**
 * Class UserCreateInputFilter
 * @package App\User\Form
 */
class UserCreateInputFilter extends InputFilter
{
    const PASSWORD_MIN_LENGTH = 6;

    /**
     * UserCreateInputFilter constructor.
     */
    public function __construct()
    {
        $this->add([
            'name' => 'firstname',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class]
            ]
        ])->add([
            'name' => 'lastname',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class]
            ]
        ])->add([
            'name' => 'email',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class]
            ],
            'validators' => [
                [
                    'name' => EmailAddress::class
                ]
            ]
        ])->add([
            'name' => 'password',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class]
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => self::PASSWORD_MIN_LENGTH
                    ]
                ],
                [
                    'name' => Identical::class,
                    'options' => [
                        'token' => 'password'
                    ]
                ]
            ]
        ])->add([
            'name' => 'passwordConfirm',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class]
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => self::PASSWORD_MIN_LENGTH
                    ]
                ],
                [
                    'name' => Identical::class,
                    'options' => [
                        'token' => 'password'
                    ]
                ]
            ]
        ]);
    }
}
