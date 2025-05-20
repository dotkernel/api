<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\ImageInput;
use Core\App\InputFilter\AbstractInputFilter;

/**
 * @phpstan-type UpdateAvatarDataType array{
 *     avatar: non-empty-string,
 * }
 * @extends AbstractInputFilter<UpdateAvatarDataType>
 */
class UpdateAvatarInputFilter extends AbstractInputFilter
{
    public function __construct()
    {
        $this->add(new ImageInput('avatar'));
    }
}
