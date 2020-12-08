<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Api\App\Common\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\Validator\EmailAddress;

/**
 * Class RecoverIdentityInputFilter
 * @package Api\User\Form\InputFilter
 */
class RecoverIdentityInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilter|\Laminas\InputFilter\InputFilterInterface|null
     */
    public function getInputFilter()
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
