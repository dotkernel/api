<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterAwareTrait;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class CreateAccountInputFilter
 * @package Api\User\Form\InputFilter
 */
class CreateAccountInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        if (empty($this->inputFilter)) {
            $this->inputFilter = (new CreateUserInputFilter())->getInputFilter();
            $this->inputFilter->setValidationGroup(['email', 'password', 'passwordConfirm', 'detail']);
        }

        return $this->inputFilter;
    }
}
