<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Api\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\NotEmpty;

/**
 * Class ActivateAccountInputFilter
 * @package Api\User\Form\InputFilter
 */
class ActivateAccountInputFilter implements InputFilterAwareInterface
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
                'name' => 'email',
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
        }

        return $this->inputFilter;
    }
}
