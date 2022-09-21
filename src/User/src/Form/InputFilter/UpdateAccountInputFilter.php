<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\InputFilter\InputFilterInterface;

/**
 * Class UpdateAccountInputFilter
 * @package Api\User\Form\InputFilter
 */
class UpdateAccountInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter(): InputFilterInterface
    {
        if (empty($this->inputFilter)) {
            $this->inputFilter = (new UpdateUserInputFilter())->getInputFilter();
            $this->inputFilter->setValidationGroup(['password', 'passwordConfirm', 'detail']);
        }

        return $this->inputFilter;
    }
}
