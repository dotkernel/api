<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\User\InputFilter\Input\AvatarInput;
use Laminas\InputFilter\InputFilter;

/**
 * @extends InputFilter<object>
 */
class UpdateAvatarInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(new AvatarInput('avatar'));
    }
}
