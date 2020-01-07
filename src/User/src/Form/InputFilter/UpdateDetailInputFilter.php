<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\OptionalInputFilter;

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
    public function getInputFilter()
    {
        if (empty($this->inputFilter)) {
            $this->inputFilter = new OptionalInputFilter();
            $this->inputFilter->add([
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
            ]);
        }

        return $this->inputFilter;
    }
}
