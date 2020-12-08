<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\InputFilter\InputFilterInterface;

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
            $this->inputFilter->setValidationGroup(['identity', 'password', 'passwordConfirm', 'detail']);
        }

        return $this->inputFilter;
    }
}
