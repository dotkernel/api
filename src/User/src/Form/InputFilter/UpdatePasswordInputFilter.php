<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\Identical;
use Laminas\Validator\StringLength;

/**
 * Class UpdatePasswordInputFilter
 * @package Api\User\Form\InputFilter
 */
class UpdatePasswordInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter(): InputFilterInterface
    {
        if (empty($this->inputFilter)) {
            $this->inputFilter = new InputFilter();
            $this->inputFilter->add([
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
                            'min' => CreateUserInputFilter::PASSWORD_MIN_LENGTH
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
                            'min' => CreateUserInputFilter::PASSWORD_MIN_LENGTH
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

        return $this->inputFilter;
    }
}
