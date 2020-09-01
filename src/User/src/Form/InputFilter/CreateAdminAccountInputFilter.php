<?php

declare(strict_types=1);

namespace Api\User\Form\InputFilter;

use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterAwareTrait;
use Laminas\InputFilter\InputFilterInterface;

/**
 * Class CreateAdminAccountInputFilter
 * @package Api\User\Form\InputFilter
 */
class CreateAdminAccountInputFilter implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        if (empty($this->inputFilter)) {
            $this->inputFilter = (new CreateAdminInputFilter())->getInputFilter();
            $this->inputFilter->setValidationGroup(['identity', 'password', 'passwordConfirm']);
        }

        return $this->inputFilter;
    }
}
