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
 * Class UserUpdateInputFilter
 * @package App\User\Form
 */
class UserUpdateInputFilter extends InputFilter
{
    /**
     * UserUpdateInputFilter constructor.
     */
    public function __construct()
    {
        $this->add([
            'name' => 'firstname',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class]
            ]
        ])->add([
            'name' => 'lastname',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class]
            ]
        ])->add([
            'name' => 'email',
            'required' => false,
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
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class]
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => UserCreateInputFilter::PASSWORD_MIN_LENGTH
                    ]
                ],
                [
                    'name' => Identical::class,
                    'options' => [
                        'token' => 'passwordConfirm'
                    ]
                ]
            ]
        ])->add([
            'name' => 'passwordConfirm',
            'required' => false,
            'filters' => [
                ['name' => StringTrim::class],
                ['name' => StripTags::class]
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => UserCreateInputFilter::PASSWORD_MIN_LENGTH
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
