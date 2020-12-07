<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Api\App\Common\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;

/**
 * Class ResetPasswordInputFilter
 * @package Api\User\Form\InputFilter
 */
class ResetPasswordInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        if (empty($this->inputFilter)) {
            $this->inputFilter = new InputFilter();
            $this->inputFilter->add([
                'name' => 'email',
                'required' => false,
                'filters' => [
                    ['name' => StringTrim::class],
                    ['name' => StripTags::class]
                ],
                'validators' => [
                    [
                        'name' => EmailAddress::class,
                        'break_chain_on_failure' => false,
                        'options' => [
                            'message' => Message::INVALID_EMAIL
                        ]
                    ]
                ]
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
                        'break_chain_on_failure' => false,
                        'options' => [
                            'message' => Message::VALIDATOR_REQUIRED_FIELD
                        ]
                    ]
                ]
            ]);
        }

        return $this->inputFilter;
    }
}
