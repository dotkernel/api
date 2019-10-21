<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Api\App\Common\Message;
use Api\User\Entity\UserEntity;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\CollectionInputFilter;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterAwareTrait;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\File\IsImage;
use Zend\Validator\File\UploadFile;
use Zend\Validator\Identical;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

use function sprintf;

/**
 * Class UpdateUserInputFilter
 * @package Api\User\Form\InputFilter
 */
class UpdateUserInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter()
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
                            'message' => Message::VALIDATOR_REQUIRED_FIELD
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
                'name' => 'email',
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
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
                            'min' => CreateUserInputFilter::PASSWORD_MIN_LENGTH
                        ]
                    ], [
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
                            'haystack' => UserEntity::STATUSES,
                            'message' => sprintf(Message::INVALID_VALUE, 'status')
                        ]
                    ]
                ]
            ])->add([
                'name' => 'isDeleted',
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
                            'haystack' => ['false', 'true'],
                            'message' => sprintf(Message::INVALID_VALUE, 'isDeleted')
                        ]
                    ]
                ]
            ])->add([
                'name' => 'avatar',
                'type' => FileInput::class,
                'required' => false,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => Message::VALIDATOR_REQUIRED_FIELD
                        ]
                    ], [
                        'name' => UploadFile::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => Message::VALIDATOR_REQUIRED_UPLOAD
                        ]
                    ], [
                        'name' => IsImage::class,
                        'break_chain_on_failure' => true,
                        'options' => [
                            'message' => Message::RESTRICTION_IMAGE
                        ]
                    ],
                ]
            ])->add($rolesCollection, 'roles')->add((new UpdateDetailInputFilter())->getInputFilter(), 'detail');
        }

        return $this->inputFilter;
    }
}
