<?php

declare(strict_types=1);

namespace Api\Admin\Form\InputFilter;

use Api\Admin\Entity\Admin;
use Api\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\Identical;
use Laminas\Validator\InArray;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

/**
 * Class UpdateAdminInputFilter
 * @package Api\Admin\Form\InputFilter
 */
class UpdateAdminInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter(): InputFilterInterface
    {
        if (empty($this->inputFilter)) {
            $rolesInputFilter = new InputFilter();
            $rolesInputFilter->add([
                'name' => 'uuid',
                'required' => true,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ],
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => Message::VALIDATOR_SKIP_OR_FILL
                        ]
                    ]
                ]
            ]);

            $rolesCollection = new CollectionInputFilter();
            $rolesCollection->setInputFilter($rolesInputFilter);
            $rolesCollection->setIsRequired(false);

            $this->inputFilter = new InputFilter();
            $this->inputFilter->add([
                'name' => 'uuid',
                'required' => false
            ])->add([
                'name' => 'identity',
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ],
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => Message::VALIDATOR_SKIP_OR_FILL
                        ]
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
                            'min' => CreateAdminInputFilter::PASSWORD_MIN_LENGTH
                        ]
                    ], [
                        'name' => NotEmpty::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => Message::VALIDATOR_SKIP_OR_FILL
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
                        'name' => Identical::class,
                        'options' => [
                            'token' => 'password'
                        ]
                    ]
                ]
            ])->add([
                'name' => 'status',
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ],
                'validators' => [
                    [
                        'name' => InArray::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'haystack' => Admin::STATUSES,
                            'message' => sprintf(Message::INVALID_VALUE, 'status')
                        ]
                    ]
                ]
            ])->add([
                'name' => 'firstName',
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ]
            ])->add([
                'name' => 'lastName',
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ]
            ])->add($rolesCollection, 'roles');
        }

        return $this->inputFilter;
    }
}
