<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Api\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\OptionalInputFilter;
use Laminas\Validator\EmailAddress;

/**
 * Class UpdateDetailInputFilter
 * @package Api\User\Form\InputFilter
 */
class UpdateDetailInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter(): InputFilterInterface
    {
        if (empty($this->inputFilter)) {
            $this->inputFilter = new OptionalInputFilter();
            $this->inputFilter->add([
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
            ])->add([
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
            ]);
        }

        return $this->inputFilter;
    }
}
