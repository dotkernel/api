<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterAwareTrait;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\OptionalInputFilter;

/**
 * Class CreateDetailInputFilter
 * @package Api\User\Form\InputFilter
 */
class CreateDetailInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        if (empty($this->inputFilter)) {
            $this->inputFilter = new OptionalInputFilter();
            $this->inputFilter->add([
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
            ]);
        }

        return $this->inputFilter;
    }
}
